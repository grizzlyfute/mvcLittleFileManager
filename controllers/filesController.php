<?php
require_once('controllers/baseController.php');
require_once('config.php');
require_once('utils/utils.php');
require_once('utils/compress.php');
require_once('utils/translator.php');
require_once('exceptions/forbiddenException.php');
require_once('models/filesfolders.php');
require_once('models/permission.php');

class FilesController extends BaseController
{
	private function getDetailedError() : string
	{
		global $CONFIG;
		$details = '';
		if ($CONFIG['debug'])
		{
			$error = error_get_last();
			if ($error)
			{
				$details = htmlspecialchars('' .
					$error['file'] . ':' . $error['line'] . ': ' .
					$error['message']);
			}
		}
		return $details;
	}

	private function getPath(string $paramName = 'p') : string
	{
		if (isset ($_REQUEST[$paramName])) $path = $_REQUEST[$paramName];
		else $path = '';
		$path = utils_cleanPath($path);
		return $path;
	}

	private function getRealPath(string $path) : string
	{
		global $CONFIG;
		$syspath = realpath($CONFIG['rootdirectory'] . DIRECTORY_SEPARATOR . utils_convertPathToSys($path));
		if (!$syspath)
		{
			throw new NotFoundException('"' . $path . '" does not exists');
		}
		return $syspath;
	}

	private function getParent(string $path) : string
	{
		$parent = substr($path, 0, strrpos($path, '/'));
		if (!$parent) $parent = '/';
		return $parent;
	}

	private function readDirEntries(string $syspath): array
	{
		global $CONFIG;

		$items = array();
		if (!is_dir($syspath))
		{
			throw new NotFoundException('Directory "' . $syspath . '" not found');
		}
		if ($handle = opendir($syspath))
		{
			while (($entry = readdir($handle)) !== false)
			{
				if ($entry == '.' || $entry == '..') continue;
				if (in_array($entry, $CONFIG['exclude_items'])) continue;
				if (!$CONFIG['show_hidden_files'] && substr($entry, 0, 1) === '.') continue;

				$items[] = $entry;
			}
			closedir ($handle);
		}
		else
		{
			throw new BadRequestException('Can not open dir "' . $path . '"');
		}

		return $items;
	}

	private function dateFormat(int $timestamp) : string
	{
		global $CONFIG;
		static $dateformat = null;
		static $timezone = null;
		$user = $this->getCurrentUser();

		if (!$dateformat && $user != null) $dateformat = $user->getDateFormat();
		if (!$dateformat) $dateformat = $CONFIG['dateformat'];
		if (!$dateformat) $dateformat = 'Y-m-d h:i:s';

		if (!$timezone && $user != null) $timezone = $user->getTimeZone();
		if (!$timezone) $timezone = $CONFIG['timezone'];
		if (!$timezone) $timezone = 'UTC';

		$dateTime = new DateTime ();
		$dateTime->setTimestamp($timestamp);
		$dateTime->setTimezone(new DateTimeZone($timezone));
		return $dateTime->format($dateformat);
	}

	private function makeFileDirObject($syspath, $path): FilesFolders
	{
		global $CONFIG;
		$item = new FilesFolders();
		$item->setName(basename($syspath));
		$item->setFullpath($path);
		$item->setIsDir(is_dir($syspath));
		$item->setIsLink(is_link($syspath));
		$item->setCreationDate($this->dateFormat(filectime($syspath)));
		$item->setModificationDate($this->dateFormat(filemtime($syspath)));
		$item->setPermissions(substr(decoct(fileperms($syspath)), -4));
		if (!$item->getIsDir())
		{
			$item->setSize(filesize($syspath));
			$item->setExtension(utils_getExtension($syspath));
		}
		else
		{
			$item->setSize(0);
			$item->setExtension('');
		}
		if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid'))
		{
			$item->setOwner(posix_getpwuid(fileowner($syspath))['name']);
			$item->setGroup(posix_getgrgid(filegroup($syspath))['name']);
		}
		else
		{
			$item->setOwner('?');
			$item->setGroup('?');
		}
		return $item;
	}

	/**
	 * Delete  file or folder (recursively)
	 * @param string $path
	 * @return bool
	 */
	private function rdelete(string $syspath): bool
	{
		if (is_link($syspath))
		{
			return unlink($syspath);
		}
		elseif (is_dir($syspath))
		{
			$objects = scandir($syspath);
			$ok = true;
			if (is_array($objects))
			{
				foreach ($objects as $file)
				{
					if ($file != '.' && $file != '..')
					{
						if (!$this->rdelete($syspath . DIRECTORY_SEPARATOR . $file))
						{
							$ok = false;
						}
					}
				}
			}
			return ($ok) ? rmdir($syspath) : false;
		}
		elseif (is_file($syspath))
		{
			return unlink($syspath);
		}
		return false;
	}

	/**
	 * Recursive chmod
	 * @param string $path
	 * @param int $filemode
	 * @param int $dirmode
	 * @return bool
	 */
	// $$$ not used
	private function rchmod(string $syspath, int $filemode, int $dirmode): bool
	{
		if (is_dir($path))
		{
			if (!chmod($syspath, $dirmode))
			{
				return false;
			}
			$objects = scandir($syspath);
			if (is_array($objects))
			{
				foreach ($objects as $file)
				{
					if ($file != '.' && $file != '..')
					{
						if (!$this->rchmod($syspath . DIRECTORY_SEPARATOR . $file, $filemode, $dirmode))
						{
							return false;
						}
					}
				}
			}
			return true;
		}
		elseif (is_link($path))
		{
			return true;
		}
		elseif (is_file($path))
		{
			return chmod($path, $filemode);
		}
		return false;
	}

	/**
	 * Safely create folder
	 * @param string $dir
	 * @param bool $force
	 * @return bool
	 */
	private function thismkdir(string $dir, bool $force): bool
	{
		if (file_exists($dir))
		{
			if (is_dir($dir))
			{
				return true;
			}
			elseif (!$force)
			{
				return false;
			}
			// The file is a directory
			if (!unlink($dir))
			{
				return false;
			};
		}
		return mkdir($dir, 0755, true);
	}

	/**
	 * Copy file or folder (recursively).
	 * @param string $path
	 * @param string $dest
	 * @param bool $upd Update files
	 * @param bool $force Create folder with same names instead file
	 * @return bool
	 */
	private function rcopy(string $syspath, string $dest, bool $upd = true, bool $force = true): bool
	{
		if (is_dir($syspath))
		{
			if (!$this->thismkdir($dest, $force))
			{
				return false;
			}
			$objects = scandir($syspath);
			$ok = true;
			if (is_array($objects))
			{
				foreach ($objects as $file)
				{
					if ($file != '.' && $file != '..')
					{
						if (!$this->rcopy($syspath . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file))
						{
							$ok = false;
						}
					}
				}
			}
			return $ok;
		}
		elseif (is_file($syspath))
		{
			// Smart copy
			$time1 = filemtime($syspath);
			if (file_exists($dest))
			{
				$time2 = filemtime($dest);
				if ($time2 >= $time1 && $upd)
				{
					return false;
				}
			}
			$ok = copy($syspath, $dest);
			if ($ok)
			{
				touch($dest, $time1);
			}
			return $ok;
		}
		return false;
	}

	private function serveFile($syspath, $attachment)
	{
		$last_modified_time = filemtime($syspath);
		$etag = md5_file($syspath);

		// Cache control
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $last_modified_time) . ' GMT');
		header('Etag: ' .  $etag);

		// Exit if not modified
		if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) <= $last_modified_time) ||
			(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag))
		{
			header('HTTP/1.1 304 Not Modified');
			ob_end_clean();
		}
		else
		{
			header('Content-Description: File Transfer');
			header('Content-Type: ' . mime_content_type($syspath));
			if ($attachment)
			{
				// Should be download and save locally
				header('Content-Disposition: attachment; filename="' . basename($syspath) . '"');
			}
			else
			{
				header('Content-Disposition: inline');
			}
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Content-Length: ' . filesize($syspath));

			// Ask client to use cache
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Max-age: 86400');

			if (!readfile($syspath))
			{
				$this->setMessage('Fail to read file "' . basename ($syspath) . '"' . $this->getDetailedError(), 'error');
			}
		}
	}

	public function listAction()
	{
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		$entries = $this->readDirEntries($syspath);
		$items = array();
		foreach ($entries as $entry)
		{
			$objSysPath = $syspath . DIRECTORY_SEPARATOR . $entry;
			$items[] = $this->makeFileDirObject($objSysPath, ($path != '/' ? $path : '') . '/' . $entry);
		}

		usort($items, function($x, $y)
		{
			if ($x->getIsDir() == $y->getIsDir()) return strcmp($x->getName(), $y->getName());
			else if ($y->getIsDir()) return 1;
			else return -1;
		});

		$this->view('files/list.php', array ('curpath' => $path, 'parent' => $this->getParent($path), 'items' => $items));
	}

	public function listDirJsonAction()
	{
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		$entries = $this->readDirEntries($syspath);
		$items = array();
		foreach ($entries as $entry)
		{
			$objSysPath = $syspath . DIRECTORY_SEPARATOR . $entry;
			if (!is_dir($objSysPath)) continue;
			$items[] = $entry;
		}

		sort($items);

		$this->echoJsonAndExit($items);
	}

	public function imagesgridAction()
	{
		global $CONFIG;
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		$basedir = dirname($syspath);
		$pathBaseDir = utils_concertSysToPath($CONFIG['rootdirectory'], $basedir);
		$entries = $this->readDirEntries($basedir);
		$imgExt = utils_getThumbnailExts();
		$curIndex = 0;
		$items = array();
		foreach ($entries as $entry)
		{
			$objSysPath = $basedir . DIRECTORY_SEPARATOR . $entry;
			$ext = utils_getExtension($objSysPath);
			$isDir = is_dir($objSysPath);
			if (!$isDir && in_array($ext, $imgExt))
			{
				$item = $this->makeFileDirObject($objSysPath, $pathBaseDir . '/' . $entry);
				$items[] = $item;
			}
		}

		usort($items, function($x, $y)
		{
			if ($x->getIsDir() == $y->getIsDir()) return strcmp($x->getName(), $y->getName());
			else if ($y->getIsDir()) return 1;
			else return -1;
		});

		for ($i = count($items) - 1; $i >= 0; --$i)
		{
			if ($items[$i]->getFullPath() == $path)
			{
				$curIndex = $i;
				break;
			}
		}

		$this->view('files/imagesgrid.php', array
		(
			'curpath' => $path,
			'parent' => $this->getParent($path),
			'items' => $items,
			'curindex' => $curIndex ,
		));
	}

	public function viewAction()
	{
		global $CONFIG;
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$archive_totalfiles = null;
		$archive_totalsize = null;
		$archive_filenames = null;
		$file = null;
		$mimetype = null;
		$next = null;
		$online = null;
		$previous = null;
		$type = null;
		$archive_ratio = null;
		$archive_sizeinarchive = null;
		$image_resolution = null;

		$syspath = $this->getRealPath($path);
		if (!is_file($syspath))
		{
			throw new NotFoundException('File "' . $path . '" not found');
		}

		$basedir = dirname($syspath);
		$files = $this->readDirEntries($basedir);
		$files = array_filter($files, function ($entry) use ($basedir) { return !is_dir($basedir . DIRECTORY_SEPARATOR . $entry ); });
		$files = array_values($files); // Re-index array
		natcasesort($files);

		// Generate prev and next if any
		$next_page_ind = array_search (basename($syspath), $files);
		if ($next_page_ind !== false)
		{
			$file = $this->makeFileDirObject($basedir . DIRECTORY_SEPARATOR . $files[$next_page_ind], $path);

			if ($next_page_ind > 0) $previous_page_ind = $next_page_ind - 1;
			else $previous_page_ind = count($files) - 1;
			$previous = $this->makeFileDirObject($basedir . DIRECTORY_SEPARATOR . $files[$previous_page_ind], substr($path, 0, strrpos($path, '/', -1)) . '/' . $files[$previous_page_ind]);

			$next_page_ind += 1;
			if ($next_page_ind >= count($files)) $next_page_ind = 0;
			$next = $this->makeFileDirObject($basedir . DIRECTORY_SEPARATOR . $files[$next_page_ind], substr($path, 0, strrpos($path, '/', -1)) . '/' . $files[$next_page_ind]);
		}
		else
		{
			// We got a problem
			throw new NotFoundException('File "' . $path . '" not found');
		}

		$ext = utils_getExtension($syspath);
		$mimetype = utils_getMimeType($syspath);
		if ($CONFIG['onlineviewer'] && $CONFIG['onlineviewer'] != 'none' &&
			in_array ($ext, utils_getOnlineViewerExts()))
		{
			// google | microsoft
			$online = $CONFIG['onlineviewer'];
		}
		else
		{
			$online = false;
		}
		if (in_array($ext, utils_getArchiveExts()))
		{
			$type = 'archive';

			$archive_filenames = getArchiveInfo($syspath, $ext);
			$archive_totalfiles = 0;
			$archive_sizeinarchive = 0;
			$archive_totalsize = 0;
			foreach ($archive_filenames as $fn)
			{
				if (!$fn['folder'])
				{
					$archive_totalfiles++;
				}
				$archive_sizeinarchive += $fn['compressed_size'];
				$archive_totalsize += $fn['filesize'];
				// 'phpinfo' ->GD requiered
			}
			$archive_ratio = $archive_totalsize > 0 ? round($archive_sizeinarchive / $archive_totalsize, 2) : 0;
		}
		elseif ($ext == 'pdf')
		{
			$type = 'pdf';
		}
		elseif (in_array($ext, utils_getImageExts()))
		{
			$type = 'image';
			$image_size = getimagesize($syspath);
			$image_resolution = (isset($image_size[0]) ? $image_size[0] : '0') . ' &times; ' . (isset($image_size[1]) ? $image_size[1] : '0');
		}
		elseif (in_array($ext, utils_getAudioExts()))
		{
			$type = 'audio';
		}
		elseif (in_array($ext, utils_getVideoExts()))
		{
			$type = 'video';
		}
		elseif (in_array($ext, utils_getTextExts()) ||
			substr($mimetype, 0, 4) == 'text' || in_array($mimetype, utils_getTextExtraMimes()))
		{
			$type = 'text';
		}
		else
		{
			$type = '';
		}

		$this->view('files/view.php', array
		(
			'parent' => $this->getParent($path),
			'archive_ratio' => $archive_ratio,
			'archive_sizeinarchive' => $archive_sizeinarchive,
			'archive_totalfiles' => $archive_totalfiles,
			'archive_totalsize' => $archive_totalsize,
			'archive_filenames' => $archive_filenames,
			'file' => $file,
			'image_resolution' => $image_resolution,
			'mimetype' => $mimetype,
			'next' => $next,
			'online' => $online,
			'previous' => $previous,
			'type' => $type,
		));
	}

	public function fileAction()
	{
		global $CONFIG;
		$action = '';
		if (isset ( $_REQUEST['fileAction']))
		{
			$action = $_REQUEST['fileAction'];
		}
		$parent = $this->getPath('parent');
		// Input/Output is supposed relative to userRoot
		$userRootPath = $this->getCurrentUser() != null ?
			$this->getCurrentUser()->getRootDirectory() : '/';
		if (isset($_REQUEST['fileActionSrcPath']) && $_REQUEST['fileActionSrcPath'])
		{
			$srcPathes = explode ('; ', ($_REQUEST['fileActionSrcPath']));
			array_walk($srcPathes, function(&$x) use ($parent, $userRootPath)
			{
				if (substr($x, 0, 1) == '/')
				{
					if ($userRootPath != '/') $path = $userRootPath . $x;
					else $path = $x;
				}
				else
				{
					if ($parent != '/') $path = $parent . '/' . $x;
					else $path = '/' . $x;
				}
				if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
				{
					throw new ForbiddenException('Access denied');
				}
				$x = $this->getRealPath($path);
			}, null);
		}
		else
		{
			throw new BadRequestException ('fileActionSrcPath not defined');
		}
		if (isset($_REQUEST['fileActionDstPath']) && $_REQUEST['fileActionDstPath'])
		{
			$dstPath = $_REQUEST['fileActionDstPath']; // no utils_cleanPath, because may be relative
			$dstPathName = $dstPath;
			if (substr ($dstPath, 0, 1) == '/')
				$dstPath = $userRootPath . $dstPath;
			else
				$dstPath = $parent . '/' . $dstPath;
			$dstPath = utils_cleanPath($dstPath);
			if (!$this->getPermissions()->isGranted(Permission::MODIFY, $dstPath))
			{
				throw new ForbiddenException('Access denied');
			}
			// no GetRealPath because dest not exists yet
			$dstPath = $CONFIG['rootdirectory'] . DIRECTORY_SEPARATOR . utils_convertPathToSys($dstPath);
		}

		$ret = true;
		$err = '';
		switch ($action)
		{
			case 'copy':
				if (count ($srcPathes) > 1)
				{
					if (!is_dir($dstPath) && !mkdir($dstPath))
					{
						$err = '"' . $dstPathName . '" can not create directory';
						$ret = false;
					}
					else
					{
						foreach ($srcPathes as $srcPath)
						{
							$ret &= $this->rcopy($srcPath, $dstPath . DIRECTORY_SEPARATOR . basename($srcPath));
						}
					}
				}
				else if (!empty($srcPathes))
				{
					$ret = $this->rcopy($srcPathes[0], $dstPath);
				}
				else
				{
					$err = 'No source file';
					$ret = false;
				}
				break;

			case 'moverename':
				if (count ($srcPathes) > 1)
				{
					if (!is_dir($dstPath) && !mkdir($dstPath))
					{
						$err = '"' . $dstPathName . '" can not create directory';
						$ret = false;
					}
					else
					{
						foreach ($srcPathes as $srcPath)
						{
							$ret &= rename($srcPath, $dstPath . DIRECTORY_SEPARATOR . basename($srcPath));
						}
					}
				}
				else if (!empty($srcPathes))
				{
					$ret = rename($srcPathes[0], $dstPath);
				}
				else
				{
					$err = 'No source file';
					$ret = false;
				}
				break;

			case 'delete':
				foreach ($srcPathes as $srcPath)
				{
					$ret &= $this->rdelete($srcPath);
				}
				break;

			case 'compress':
				if (isset($_REQUEST['fileAction']))
				{
					if (isset ($_REQUEST['compressAction']))
					{
						$dstPath = addExtForArchive($_REQUEST['compressAction'], $dstPath);
						$ret &= packFiles($srcPathes, $dstPath, $this->getRealPath($parent), $err, $CONFIG['max_size_to_compress']);
					}
					else
					{
						$ret = false;
					}
				}
				break;

			case 'uncompress':
				if (!is_dir($dstPath) && !mkdir($dstPath, 755, false))
				{
					$err = '"' . $dstPathName . '" is not a directory';
					$ret = false;
				}
				else
				{
					foreach ($srcPathes as $srcPath)
					{
						$ret &= unpackFiles($srcPath, $dstPath, $err);
						if (!$ret) $this->setMessage($err, 'error');
					}
				}
				break;

			case 'downloadzip':
				$tmpName = null;
				$cnt = count ($srcPathes);
				$prefix = $this->dateFormat(time()) . '_';
				if ($cnt == 1)
				{
					$prefix .= str_replace(array('.', '/', '\\', '"', '*', '?', '!', ';', ':', '<', '>'), '_', basename($srcPathes[0])) . '_';
					$tmpName = tempnam($CONFIG['tmppath'], $prefix) . '.zip';
				}
				else if ($cnt > 1)
				{
					$prefix .= str_replace(array('.', '/', '\\', '"', '*', '?', '!', ';', ':', '<', '>'), '_', basename($this->getRealPath($parent))) . '_';
					$tmpName = tempnam($CONFIG['tmppath'], $prefix) . '.zip';
				}
				if (!$tmpName)
				{
					$this->setMessage('Can not create temp zip');
					$ret = false;
				}
				else
				{
					$ret &= packFiles($srcPathes, $tmpName, $this->getRealPath($parent), $err, $CONFIG['max_size_to_compress']);
					if ($ret)
					{
						try
						{
							$this->serveFile($tmpName, true);
						}
						finally
						{
							unlink($tmpName);
						}
						return;
					}
				}
				break;

			default:
				$ret = false;
				break;
		}

		if ($ret)
		{
			$this->setMessage($this->getTranslator()->translate('common.success'), 'success');
		}
		else
		{
			$this->setMessage('An error occurs during operation "' .  $action . '" ' . $err . PHP_EOL . $this->getDetailedError() . PHP_EOL, 'error');
		}

		$this->redirect('?action=ls&p=' . rawurlencode($parent));
	}

	public function newItemAction()
	{
		$parent = $this->getPath('parent');
		if (!$this->getPermissions()->isGranted(Permission::MODIFY, $parent))
		{
			throw new ForbiddenException('Access denied');
		}
		$filename = utils_cleanPath($_REQUEST['newitemname']);
		$syspath = $this->getRealPath($parent) . DIRECTORY_SEPARATOR . $filename;
		$type = $_REQUEST['itemtype'];
		if ($type == 'file')
		{
			if (is_file($syspath) || touch($syspath))
			{
				$this->setMessage ($this->getTranslator()->translate('common.success'), 'success');
			}
			else
			{
				$this->setMessage('Fail to create file "' . $parent . '/' . $filename . '"' . $this->getDetailedError(), 'error');
			}
		}
		else if ($type == 'folder')
		{
			if (is_dir($syspath) || mkdir($syspath))
			{
				$this->setMessage ($this->getTranslator()->translate('common.success'), 'success');
			}
			else
			{
				$this->setMessage('Fail to create folder "' . $parent . '/' . $filename . '"' . $this->getDetailedError(), 'error');
			}
		}
		else
		{
			$this->setMessage('Unknown item type "' . $type . '"', 'error');
		}

		$this->redirect('?action=ls&p=' . rawurlencode($parent));
	}

	public function editAction()
	{
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::MODIFY, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		if (!is_file($syspath))
		{
			throw new NotFoundException ('File "' . $path . '" is not found');
		}
		$file = $this->makeFileDirObject($syspath, $path);

		// Normal editor
		$isNormalEditor = true;
		if (isset($_GET['env']))
		{
			if ($_GET['env'] == "ace")
			{
				$isNormalEditor = false;
			}
		}

		// Save File
		if (isset($_POST['savedata']))
		{
			$writedata = $_POST['savedata'];
			$fd = fopen($syspath, "w");
			if (!$fd)
			{
				$this->setMessage('Fail to open  "' . $path . '" in  write mode' . $this->getDetailedError(), 'error');
			}
			// @ suppresss error messages....
			else if (!@fwrite($fd, $writedata))
			{
				$this->setMessage('Fail to write data of "' . $path . '"' . $this->getDetailedError(), 'error');
			}
			if ($fd)
			{
				if (!fclose($fd))
				{
					$this->setMessage('Fail to close "' . $path . '"' . $this->getDetailedError(), 'error');
				}
				else
				{
					$this->setMessage('File saved successfully', 'success');
				}
			}
		}

		$ext = strtolower(pathinfo($syspath, PATHINFO_EXTENSION));
		$mime_type = utils_getMimeType($syspath);
		$is_text = false;
		$content = ''; // for text

		if (in_array($ext, utils_getTextExts()) ||
			substr($mime_type, 0, 4) == 'text' ||
			in_array($mime_type, utils_getTextExtraMimes()))
		{
			$is_text = true;
			$content = file_get_contents($syspath);
		}

		$this->view('files/edit.php', array
		(
			'istext' => $is_text,
			'isnormaleditor' => $isNormalEditor,
			'content' => $content,
			'file' => $file,
		));
	}

	public function thumbnailAction()
	{
		global $CONFIG;
		$THUMBNAIL_MAXX = 128;
		$THUMBNAIL_MAXY = 128;

		$err = null;
		$srcImage = null;
		$dstImage = null;
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}

		// Extract file info
		$syspath = $this->getRealPath($path);
		$relativeSystemPath = substr($syspath, strlen($CONFIG['rootdirectory']));
		$thumbnailPath = APPDATAPATH . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR . $relativeSystemPath;

		// Check config
		if (!$CONFIG['thumbnail'])
		{
			// $err = 'thumbnail desactivated';
			$thumbnailPath = $syspath;
			goto end;
		}
		if (!function_exists('getimagesize') || !function_exists('gd_info'))
		{
			// $err = 'No GD extention';
			$thumbnailPath = $syspath;
			goto end;
		}

		// If exists, serve the current thumbnail
		if (file_exists($thumbnailPath))
		{
			// TODO Check if thumbnail is older than file modification
			if (filemtime ($thumbnailPath) < filemtime($syspath))
			{
				unlink($thumbnailPath);
			}
			else
			{
				// This is not an error
				goto end;
			}
		}
		else
		{
			$basedir = dirname($thumbnailPath);
			if (!is_dir ($basedir))
			{
				if (!mkdir ($basedir, 0700, true))
				{
					$err = 'Can not create directory "' . $basedir . '" ' . $this->getDetailedError();
					goto end;
				}
			}
		}

		$ext = utils_getExtension($syspath);
		if (!in_array($ext, utils_getThumbnailExts()))
		{
			$err = 'file "' . $path . '" has no thumbnail extention';
			goto end;
		}
		if ($ext == 'svg')
		{
			// Serve file directly
			$thumbnailPath = $syspath;
			goto end;
		}
		$imageInfo = getimagesize($syspath);
		if (!is_array($imageInfo))
		{
			$err = 'Can not analyse image of file "' . $path . '"';
			goto end;
		}
		$srcWidth = $imageInfo[0];
		$srcHeight = $imageInfo[1];
		$type = $imageInfo[2];
		if ($srcWidth < 0 || $srcHeight < 0)
		{
			$err = 'Invalid size';
			goto end;
		}
		$extratorFn = null;
		$writerFn = null;
		switch ($type)
		{
			case IMG_BMP:
				$extratorFn = 'imagecreatefrombmp';
				$writerFn = 'imagebmp';
				break;
			case IMG_GIF:
				$extractorFn = 'imagecreatefromgif';
				$writerFn = 'imagegif';
				break;
			case IMG_JPG:
				//IMG_JPEG:
				$extractorFn = 'imagecreatefromjpeg';
				$writerFn = 'imagejpeg';
				break;
			case IMG_PNG:
				$extractorFn = 'imagecreatefrompng';
				$writerFn = 'imagepng';
				break;
			case IMG_WBMP:
				$extractorFn = 'imagecreatefromwbmp';
				$writerFn = 'imagewbmp';
				break;
			case IMG_XPM:
				$extractorFn = 'imagecreatefromxpm';
				$writerFn = 'imagexpm';
				break;
			case IMG_WEBP:
				$extractorFn = 'imagecreatefromwebp';
				$writerFn = 'imagewebp';
				break;
			default:
				$err = 'Invalid image';
				goto end;
				break;
		}
		if ($srcHeight < $THUMBNAIL_MAXY && $srcWidth < $THUMBNAIL_MAXX)
		{
			// Image is two small, no need to thumb.
			$thumbnailPath = $syspath;
			goto end;
		}
		if ($srcHeight > $srcWidth)
		{
			$dstHeight = $THUMBNAIL_MAXY;
			$dstWidth = floor ($srcWidth * $dstHeight / $srcHeight);
		}
		else
		{
			$dstWidth = $THUMBNAIL_MAXX;
			$dstHeight = floor ($srcHeight * $dstWidth / $srcWidth);
		}
		$srcImage = call_user_func($extractorFn, $syspath);
		if (!$srcImage)
		{
			$err = 'Can not open source image "' . $path . '" ' . $this->getDetailedError();
			goto end;
		}
		$dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
		if (!$dstImage)
		{
			$err = 'Can not create destination image ' . $this->getDetailedError();
			goto end;
		}
        if (!imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight))
		{
			$err = 'Can not resample image';
			goto end;
		}
		if (!call_user_func($writerFn, $dstImage, $thumbnailPath))
		{
			$err = 'Can not write dest image "' . $thumbnailPath . '" ' . $this->getDetailedError();
			goto end;
		}

		end:
		if ($dstImage) imagedestroy($dstImage);
		if ($srcImage) imagedestroy($srcImage);

		if ($err != null)
		{
			//$this->serveFile($syspath, false);
			$this->setMessage($err, 'error');
		}
		else
		{
			$this->serveFile($thumbnailPath, false);
		}
	}

	public function downloadAction()
	{
		global $CONFIG;
		$parent = $this->getPath('parent');
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::VIEW, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		if (is_file($syspath) || is_link($syspath))
		{
			$this->serveFile($syspath, isset($_GET['dlf']));
		}
		else if (is_dir($syspath))
		{
			$temp_file = tempnam($CONFIG['tmppath'], basename($syspath) . '_') . '.zip';
			$err = '';
			if (!packFiles(array($syspath), $temp_file,  $this->getRealPath($this->getParent($path)), $err, $CONFIG['max_size_to_compress']))
			{
				$this->setMessage('Can not pack "' . $path . '" ' . $err . ' ' .
				  	$this->getDetailedError(), 'error');
				$this->redirect('?action=ls&p=' . rawurlencode($parent));
			}
			else
			{
				try
				{
					$this->serveFile($temp_file, isset($_GET['dlf']));
				}
				finally
				{
					unlink($temp_file);
				}
			}
		}
		else
		{
			throw new NotFoundException('"' . $path . '" is not a regular file');
		}
	}

	public function uploadAction()
	{
		global $CONFIG;
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::MODIFY, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		if (!is_dir($syspath))
		{
			throw new BadRequestException('"' . $path . '" is not a directory');
		}

		// Upload using url
		if (isset($_REQUEST["uploadurl"]) &&
			// AJAX Request
			(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
		{
			$url = !empty($_REQUEST["uploadurl"]) && preg_match("|^http(s)?://.+$|", stripslashes($_REQUEST["uploadurl"])) ? stripslashes($_REQUEST["uploadurl"]) : null;
			$temp_file = tempnam($CONFIG['tmppath'], "upload-");
			$fileinfo = new stdClass();
			$fileinfo->name = trim(basename($url), ".\x00..\x20");

			$err = false;
			$use_curl = false;
			if (!$url)
			{
				$success = false;
			}
			else if ($use_curl)
			{
				$fp = fopen($temp_file, "w");
				if (!$fp)
				{
					$err = 'Fail to read file "' . $path . '"' . $this->getDetailedError();
					$success = false;
				}
				else
				{
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_NOPROGRESS, false );
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					$success = curl_exec($ch);
					$curl_info = curl_getinfo($ch);
					if (!$success)
					{
						$err = 'Curl failed (' .$path . ')' . ($CONFIG['debug'] ? (': ' . curl_error($ch)) : '');
					}
					curl_close($ch);
					fclose($fp);
					$fileinfo->size = $curl_info["size_download"];
					$fileinfo->type = $curl_info["content_type"];
				}
			}
			else
			{
				$ctx = stream_context_create();
				if (!$ctx)
				{
					$success = false;
				}
				else
				{
					$success = copy($url, $temp_file, $ctx);
				}
				if (!$success)
				{
					$err = 'Fail to read file "' . $path . '"' . $this->getDetailedError();
				}
			}

			if ($success)
			{
				$destPath = $syspath . DIRECTORY_SEPARATOR . $fileinfo->name;
				while (file_exists($destPath))
				{
					$ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
					$ext_1 = $ext ? '.'.$ext : '';
					$destPath = str_replace($ext_1, '', $destPath) . '_' . date('ymdHis'). $ext_1;
				}
				$success = rename($temp_file, $destPath);
				if (!$success)
				{
					$err = 'Fail to rename to "' . $fileinfo->name . '"' . $this->getDetailedError();
				}
			}

			if ($success)
			{
				$this->echoJsonAndExit(array('done' => $fileinfo));
			}
			else
			{
				unlink($temp_file);
				if (!$err)
				{
					$err = 'Invalid url parameter';
				}
				$this->echoJsonAndExit(array('fail' => $err));
			}
		}
		// Standard upload
		else if (!empty($_FILES))
		{
			$f = $_FILES;

			$filename = utils_cleanPath($_FILES['file']['name']);
			$tmp_name = utils_cleanPath($_FILES['file']['tmp_name']);

			$targetPath = $path;
			$destPath = $syspath . DIRECTORY_SEPARATOR . utils_convertPathToSys($filename);

			while (file_exists($destPath))
			{
				$ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
				$ext_1 = $ext ? '.'.$ext : '';
				$destPath = str_replace($ext_1, '', $destPath) . '_' . date('ymdHis'). $ext_1;
			}

			if ((empty($_FILES['file']['error']) || $_FILES['file']['error'] == 0) &&
				!empty($tmp_name) && $tmp_name != 'none')
			{
				if (move_uploaded_file($tmp_name, $destPath))
				{
					$this->setMessage($this->getTranslator()->translate('common.success'), 'success');
				}
				else
				{
					unlink($tmp_name);
					throw new BadRequestException('Error while uploading "' . $_FILES['file']['name'] . '"');
				}
			}
			else
			{
				throw new BadRequestException('Error while uploading "' . $_FILES['file']['name'] . '" ' . $_FILE['error']);
			}
			$fileinfo = new stdClass();
			$fileinfo->name = $filename;

			$this->echoJsonAndExit(array('done' => $fileinfo));
		}
		else
		{
			$this->echoJsonAndExit(array('fail' => 'No file provided'));
		}

		// Return slienciouly : echooing jso,
	}

	public function changePermissionsAction()
	{
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::CHANGEPERMS, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);
		$mode = fileperms($syspath);

		$this->view('files/changepermissions.php', array
		(
			'fullpath' => $path,
			'mode' => $mode,
			'parent' => $this->getParent($path),
		));
	}

	public function doChangePermissionsAction()
	{
		$path = $this->getPath();
		if (!$this->getPermissions()->isGranted(Permission::CHANGEPERMS, $path))
		{
			throw new ForbiddenException('Access denied');
		}
		$syspath = $this->getRealPath($path);

		$mode = 0;
		if (!empty($_POST['ur']))
		{
			$mode |= 0400;
		}
		if (!empty($_POST['uw']))
		{
			$mode |= 0200;
		}
		if (!empty($_POST['ux']))
		{
			$mode |= 0100;
		}
		if (!empty($_POST['gr']))
		{
			$mode |= 0040;
		}
		if (!empty($_POST['gw']))
		{
			$mode |= 0020;
		}
		if (!empty($_POST['gx']))
		{
			$mode |= 0010;
		}
		if (!empty($_POST['or']))
		{
			$mode |= 0004;
		}
		if (!empty($_POST['ow']))
		{
			$mode |= 0002;
		}
		if (!empty($_POST['ox']))
		{
			$mode |= 0001;
		}

		if (@chmod($syspath, $mode))
		{
			$this->setMessage($this->getTranslator()->translate('common.success'), 'success');
			$this->redirect('?action=ls&p=' . rawurlencode($this->getParent($path)));
		}
		else
		{
			$this->setMessage('Permissions not changed ' . PHP_EOL . $this->getDetailedError(), 'error');
			$this->redirect('?action=changepermissions&p=' . rawurlencode($this->getParent($path)));
		}
	}
}
?>
