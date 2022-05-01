<?php

require_once ('utils/utils.php');

interface ICompressor
{
	public function addEmptyDir(string $path): bool;
	public function addFile(string $path): bool;
	public function addRec(string $path): bool;
	public function compress(): bool;
	public function extractTo(string $path): bool;
	public function getErrorMessage(): string;
	public function dispose(): void;
}

class CompressorFactory
{
	// Factory
	public static function createCompressor(string $archivePath, int $level = 0)
	{
		$ext = strtolower(pathinfo($archivePath, PATHINFO_EXTENSION));
		$compressor = null;

		switch ($ext)
		{
			case 'tar':
				if (class_exists('PharData'))
				{
					$compressor = new CompressorPhar($archivePath, 'none', 0);
				}
				break;
			case 'gz':
			case 'tar.gz':
			case 'tgz':
				if (class_exists('PharData'))
				{
					$compressor = new CompressorPhar($archivePath, 'gzip');
				}
				break;
			case 'bz2':
			case 'tar.bz2':
			case 'tbz2':
				if (class_exists('PharData'))
				{
					$compressor = new CompressorPhar($archivePath, 'bzip2');
				}
				break;
			case 'zip':
				if (class_exists('ZipArchive'))
				{
					$compressor = new CompressorZip($archivePath, ZipArchive::CM_DEFAULT);
				}
				break;
			default:
				throw new \Exception ('Unsupported archive extention "' . $ext . "'");
		}

		if (!$compressor)
		{
			throw new \Exception ('No class found to manage ext "' . $ext);
		}

		return $compressor;
	}
}

abstract class ACompressor implements ICompressor
{
	public abstract function addEmptyDir(string $path): bool;
	public abstract function addFile(string $path): bool;
	public abstract function compress(): bool;
	public abstract function extractTo(string $path): bool;

	private $knownInodes = array();

	private $errorMessage = '';
	public function getErrorMessage(): string
	{
		return $this->errorMessage;
	}
	protected function setErrorMessage(string $message)
	{
		$this->errorMessage = $message;
		return $this;
	}

	public function addRec(string $path): bool
	{
		$ret = true;
		$inode = fileinode (realpath($path));
		if ($inode && in_array($inode, $this->knownInodes))
		{
			$inode = false;
		}
		else if (is_dir($path))
		{
			$objects = scandir($path);
			if (is_array($objects))
			{
				$ret &= $this->addEmptyDir($path);
			}
			else
			{
				$this->setErrorMessage('Can not scan dir ' . $path);
				$ret = false;
			}
			if ($ret)
			{
				foreach ($objects as $filename)
				{
					if ($filename == '.' || $filename == '..') continue;
					$ret &= $this->addRec($path . DIRECTORY_SEPARATOR . $filename, $this->knownInodes);
					if (!$ret) break;
				}
			}
		}
		else if (is_file($path))
		{
			$ret = $this->addFile($path);
			$this->knownInodes[] = $inode;
		}
		else
		{
			$this->setErrorMessage('Unknown file type ' . $path);
			$ret = false;
		}

		if ($inode)
		{
			$this->knownInodes[] = $inode;
		}

		return $ret;
	}

	public function dispose() : void
	{
		// To be overrided
	}
}


class CompressorZip extends ACompressor
{
	private $zipper = null;
	private $level = 0;

	// level:
	// ZipArchive::CM_DEFAULT (int) better of deflate or store.
	// ZipArchive::CM_STORE (int) stored (uncompressed).
	// ZipArchive::CM_SHRINK (int) shrunk
	// ZipArchive::CM_REDUCE_1 (int) reduced with factor 1
	// ZipArchive::CM_REDUCE_2 (int) reduced with factor 2
	// ZipArchive::CM_REDUCE_3 (int) reduced with factor 3
	// ZipArchive::CM_REDUCE_4 (int) reduced with factor 4
	// ZipArchive::CM_IMPLODE (int) imploded
	// ZipArchive::CM_DEFLATE (int) deflated
	// ZipArchive::CM_DEFLATE64 (int) deflate64
	// ZipArchive::CM_PKWARE_IMPLODE (int) PKWARE imploding
	// ZipArchive::CM_BZIP2 (int) BZIP2 algorithm
	// ZipArchive::CM_LZMA (int) LZMA algorithm
	// ZipArchive::CM_LZMA2 (int) LZMA2 algorithm. Available as of PHP 7.4.3 and PECL zip 1.16.0, respectively, if built against libzip ≥ 1.6.0.
	// ZipArchive::CM_ZSTD (int) Zstandard algorithm. Available as of PHP 8.0.0 and PECL zip 1.19.1, respectively, if built against libzip ≥ 1.8.0.
	// ZipArchive::CM_XZ (int) XZ algorithm. Available as of PHP 7.4.3 and PECL zip 1.16.1, respectively, if built against libzip ≥ 1.6.0.
	public function __construct(string $filename, int $level = 0)
	{
		$this->zipper = new ZipArchive();
		$this->level = $level;
		if (!$this->zipper->open($filename, ZipArchive::CREATE))
		{
			$this->setErrorMessage ($this->zipper->getStatusString());
			$this->zipper = null;
			throw new Exception('Can not create zip "' . $filename . '"');
		}
	}

	public function dispose(): void
	{
		if ($this->zipper)
		{
			$this->zipper->close();
			$this->zipper = null;
		}
	}

	public function __destruct()
	{
		$this->dispose();
	}


	public function addEmptyDir(string $path): bool
	{
		if (!$this->zipper) return false;
		return $this->zipper->addEmptyDir($path);
	}

	public function addFile(string $path): bool
	{
		if (!$this->zipper) return false;
		$ret = $this->zipper->addFile($path);
		$this->zipper->setCompressionName($path, $this->level);
		return $ret;
	}

	public function compress(): bool
	{
		// Done bye close
		return true;
	}

	public function extractTo(string $path): bool
	{
		if (!$this->zipper) return false;
		return $this->zipper->extractTo($path);
	}
}

class CompressorPhar extends ACompressor
{
	private $method = null;
	private $phar = null;
	public function __construct(string $filename, string $method)
	{
		$this->phar = new PharData($filename);
		switch ($method)
		{
			case 'none':
				$this->method = Phar::NONE;
				break;
			case 'gzip':
				$this->method = Phar::GZ;
				break;
			case 'bzip2':
				$this->method = Phar::BZ2;
				break;
			default:
				throw new \Exception ('Unsupported phar method "' . $method . '"');
				break;
		}
	}

	public function addEmptyDir(string $path): bool
	{
		$this->phar->addEmptyDir($path);
		return true;
	}

	public function addFile(string $path): bool
	{
		$this->phar->addFile($path);
		return true;
	}

	public function compress(): bool
	{
		$ret = true;
		if ($this->method != Phar::NONE)
		{
			try
			{
				$oldPath = $this->phar->getPath();
				$this->phar->compress($this->method);
				// Both tar and tar.gz exists
				unlink($oldPath);
			}
			catch (PharException $e)
			{
				$this->setMessage($e->getMessage());
				$ret = false;
			}
		}
		return $ret;
	}

	public function extractTo(string $path): bool
	{
		$ret = true;
		try
		{
			$ret = $this->phar->extractTo($path);
		}
		catch (PharException $e)
		{
			$this->setMessage($e->getMessage());
			$ret = false;
		}
		return $ret;
	}
}


/**
 * Get info about zip archive
 * @param string $path
 * @return array|bool
 */
function getArchiveInfo($path, $ext)
{
	if ($ext == 'zip' && function_exists('zip_open'))
	{
		$arch = zip_open($path);
		if (is_resource($arch))
		{
			$filenames = array();
			while ($zip_entry = zip_read($arch))
			{
				$zip_name = zip_entry_name($zip_entry);
				$zip_folder = substr($zip_name, -1) == DIRECTORY_SEPARATOR;
				$filenames[] = array
				(
					'name' => $zip_name,
					'filesize' => zip_entry_filesize($zip_entry),
					'compressed_size' => zip_entry_compressedsize($zip_entry),
					'folder' => $zip_folder
					//'compression_method' => zip_entry_compressionmethod($zip_entry),
				);
			}
			zip_close($arch);
			return $filenames;
		}
		else
		{
			return false;
		}
	}
	elseif (($ext == 'tar' || $ext == 'gz' || $ext == 'tgz' || $ext == 'tbz2' || $ext == 'bz2') &&
			class_exists('PharData'))
	{
		$archive = new PharData($path);
		$filenames = array();
		foreach(new RecursiveIteratorIterator($archive) as $file)
		{
			$parent_info = $file->getPathInfo();
			$zip_name = str_replace("phar://" . $path, '', $file->getPathName());
			$zip_name = substr($zip_name, ($pos = strpos($zip_name, DIRECTORY_SEPARATOR)) !== false ? $pos + 1 : 0);
			$zip_folder = false; // $parent_info->getFileName();
			$zip_info = new SplFileInfo($file);
			$filenames[] = array
			(
				'name' => $zip_name,
				'filesize' => $zip_info->getSize(),
				'compressed_size' => $file->getCompressedSize(),
				'folder' => $zip_folder
			);
		}
		return $filenames;
	}
	return false;
}

// archivecmd = 'zip/tar/targz/tarbz2'
function addExtForArchive (string $archivecmd, string $path): string
{
	$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

	switch ($archivecmd)
	{
		case 'zip':
			if ($ext != 'zip') $path .= '.zip';
			break;
		case 'tar':
			if ($ext != 'tar') $path .= '.tar';
			break;
		case 'targz':
			if ($ext != 'tgz' && $ext != 'tar.gz' && $ext != 'gz') $path .= '.tgz';
			break;
		case 'tarbz2':
			if ($ext != 'tbz' && $ext != 'tar.bz2' && $ext != 'bz2') $path .= '.tbz2';
			break;
		default:
			break;
	}
	return $path;
}

function packFiles(array $files, string $dstPath, string $parent, string &$err, int $maxSizeToCompress): bool
{
	if (empty ($files))
	{
		$err = 'Empty files set';
		return false;
	}
	$cwd = getcwd();
	if (!chdir ($parent))
	{
		$err = "Can not chdir \"$parent\"";
		return false;
	}

	$ret = true;
	$compressor = null;
	try
	{
		// check files size
		$sizeToCompress = 0;
		foreach ($files as $file)
		{
			$sizeToCompress += utils_folderSize($file, $maxSizeToCompress);
			if ($sizeToCompress > $maxSizeToCompress)
			{
				die();
				throw new \Exception ("Too big file set to compress");
			}
		}

		$compressor = CompressorFactory::createCompressor($dstPath);
		$parentlen = strlen($parent) + 1;
		foreach ($files as $file)
		{
			// If file is simlink pointing outside root directory, then not removing root
			if (utils_isPathIncludeInto ($parent, $file, false))
			{
				// Remove root
				$file = substr($file, $parentlen);
			}
			if (!$file)
			{
				throw new \Exception ('Invalid file name "' . $file . '"');
			}
			if (!$compressor->addRec($file))
			{
				throw new \Exception ('Can not add "' . $file . '"');
			}
		}
		if (!$compressor->compress())
		{
			throw new \Exception ('Can not do compress: ' . $compressor->getErrorMessage());
		}
	}
	catch (\Exception $e)
	{
		$err = 'Archive not created: ' . $e->getMessage();
		$ret = false;
	}
	finally
	{
		chdir($cwd);
		if ($compressor)
		{
			$compressor->dispose();
		}
	}

	return $ret;
}

function unpackFiles(string $archivePath, string $dstPath, string &$err): bool
{
	$ret = true;
	$compressor = null;
	try
	{
		$archivePath = utils_cleanPath($archivePath);
		$dstPath = utils_cleanPath($dstPath);
		if (!is_file($archivePath))
		{
			throw new \Exception ('"' . $archivePath . '" is not a valid file');
		}
		if (!is_dir($dstPath))
		{
			throw new \Exception ('Directory not found "' . $dstPath . '" is not a valid file');
		}
		$tofolder = pathinfo($archivePath, PATHINFO_FILENAME);
		$dstPath .= DIRECTORY_SEPARATOR . $tofolder;
		if (!mkdir($dstPath, 0755, false))
		{
			throw new \Exception ('Can not create directory "' . $dstPath . '"');
		}

		$compressor = CompressorFactory::createCompressor($archivePath);
		if (!$compressor->extractTo($dstPath))
		{
			throw new \Exception ('Can not compress: ' . $compressor->getErrorMessage());
		}
	}
	catch (\Exception $e)
	{
		$err = 'Archive not created: ' . $e->getMessage();
		$ret = false;
	}
	finally
	{
		if ($compressor)
		{
			$compressor->dispose();
		}
	}

	return $ret;
}
?>
