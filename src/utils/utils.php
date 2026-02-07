<?php

function utils_cleanPath(?string $path) : ?string
{
  if (!$path) return $path;
  $path = strval(str_replace("\0", '', $path));
  $path = trim($path);
  $path = trim($path, '/');
  $parts = array_filter(explode('/', $path), 'strlen');
  $absolutes = array();
  foreach ($parts as $part)
  {
    if ('.' == $part) continue;
    if ('..' == $part)
      array_pop($absolutes);
    else
      $absolutes[] = $part;
  }
  $path = implode('/', $absolutes);

  if (substr($path, 0, 3) === '../' ||
    substr($path, 0, 2) === './' ||
    substr($path, 0, 1) === '/')
  {
    $path = '/';
  }
  else if (substr($path, 0, 1) != '/') $path = '/' . $path;

  return $path;
}

function utils_pathToHtml(string $text) : string
{
  $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED, 'UTF-8');
  $text = str_replace ("\n", '&NewLine;', $text);
  $text = str_replace ("\r", '&#x000d;', $text);
  $text = str_replace ("\t", '&Tab;', $text);
  return $text;
}

function utils_escapeSingleQuote(string $text) : string
{
  $text = str_replace ("\\", "\\\\", $text);
  $text = str_replace ("'", "\\'", $text);
  $text = str_replace ("\n", '', $text);
  $text = str_replace ("\r", '', $text);
  $text = str_replace ("\t", '', $text);
  return $text;
}

function utils_getFileSizeSuffix($size)
{
  if ($size > 1e12) return number_format($size / 1e12, 2) . ' To';
  elseif ($size > 1e9) return number_format($size / 1e9, 2) . ' Go';
  elseif ($size > 1e6) return number_format($size / 1e6, 2) . ' Mo';
  elseif ($size > 1e3) return number_format($size / 1e3, 2) . ' ko';
  else return number_format($size, 2) . ' o';
}

// Name may not exists
function utils_getExtension(string $name): string
{
  return strtolower(pathinfo($name, PATHINFO_EXTENSION));
}

function utils_getFileIconClass($path)
{
  $ext = utils_getExtension($path);

  switch ($ext)
  {
    case 'bmp':
    case 'gif':
    case 'ico':
    case 'jp2':
    case 'jpc':
    case 'jpeg':
    case 'jpg':
    case 'jpx':
    case 'png':
    case 'svg':
    case 'tif':
    case 'tiff':
      $img = 'fas fa-image';
      break;

    case 'ai':
    case 'dia':
    case 'eps':
    case 'fla':
    case 'psd':
    case 'schm':
    case 'swf':
    case 'wbmp':
    case 'xbm':
    case 'xcf':
      $img = 'fas fa-file-image';
      break;

    case 'asm':
    case 'c':
    case 'c++':
    case 'cc':
    case 'cpp':
    case 'cs':
    case 'csh':
    case 'h':
    case 'hpp':
    case 'java':
    case 'ino':
    case 'js':
    case 'jsp':
    case 'json':
    case 'lua':
    case 'mak':
    case 'map':
    case 'md':
    case 'php':
    case 'php4':
    case 'php5':
    case 'phps':
    case 'phtml':
    case 'py':
    case 'r':
    case 'rpy':
    case 'sql':
    case 'tpl':
    case 'ts':
    case 'twig':
    case 'vba':
      $img = 'fas fa-code';
      break;

    case 'css':
    case 'htm':
    case 'html':
    case 'html5':
    case 'less':
    case 'passwd':
    case 'sass':
    case 'scss':
    case 'shtml':
    case 'xhtml':
      $img = 'fas fa-file-code';
      break;

    case 'conf':
    case 'config':
    case 'dtd':
    case 'ftpquota':
    case 'gitignore':
    case 'htaccess':
    case 'resx';
    case 'ini':
    case 'log':
    case 'txt':
    case 'lock':
      $img = 'fas fa-file-alt';
      break;

    case '7z':
    case 'a':
    case 'ar':
    case 'arc':
    case 'apk';
    case 'bz':
    case 'bz2':
    case 'cab':
    case 'cpio':
    case 'gz':
    case 'lz':
    case 'lzma':
    case 'phar':
    case 'rar':
    case 'tar':
    case 'tgz':
    case 'tbz2':
    case 'txz':
    case 'xz':
    case 'zip':
      $img = 'fas fa-file-archive';
      break;

    case 'csv':
    case 'ods':
    case 'xls':
    case 'xlsx':
    case 'xml':
    case 'xsl':
    case 'yml':
      $img = 'fas fa-file-excel';
      break;

    case 'aac':
    case 'ac3':
    case 'flac':
    case 'm4a':
    case 'mka':
    case 'mp2':
    case 'mp3':
    case 'oga':
    case 'ogg':
    case 'tds':
    case 'wav':
    case 'wma':
      $img = 'fas fa-music';
      break;

    case 'cue':
    case 'm3u':
    case 'm3u8':
    case 'mid':
    case 'midi':
    case 'pls':
    case 'weba':
      $img = 'fas fa-file-audio';
      break;

    case '3g2':
    case '3gp':
    case 'asf':
    case 'avi':
    case 'f4v':
    case 'flv':
    case 'm4v':
    case 'mkv':
    case 'mov':
    case 'mov':
    case 'mp4':
    case 'mpeg':
    case 'mpg':
    case 'ogm':
    case 'ogv':
    case 'webm':
    case 'wmv':
      $img = 'fas fa-file-video';
      break;

    case 'eml':
    case 'msg':
      $img = 'fas fa-envelope';
      break;

    case 'bak':
    case 'back':
    case 'backup':
    case 'prev':
    case 'old':
      $img = 'fas fa-clipboard';
      break;

    case 'doc':
    case 'docx':
    case 'odt':
    case 'rtf':
      $img = 'fas fa-file-word';
      break;

    case 'odp':
    case 'ppt':
    case 'pptx':
      $img = 'fas fa-file-powerpoint';
      break;

    case 'eot':
    case 'fon':
    case 'otf':
    case 'ttc':
    case 'ttf':
    case 'woff':
    case 'woff2':
      $img = 'fas fa-font';
      break;

    case 'pdf':
    case 'ps':
      $img = 'fas fa-file-pdf';
      break;

    case 'img':
    case 'iso':
    case 'raw':
      $img = 'fas fa-hdd';
      break;

    case 'bin':
    case 'com':
    case 'exe':
    case 'msi':
      $img = 'fas fa-file-invoice';
    break;

    case 'bat':
    case 'bash':
    case 'sh':
      $img = 'fas fa-terminal';
      break;

    case 'azw':
    case 'cbr':
    case 'cbz':
    case 'epub':
    case 'kcc':
      $img = 'fas fa-book';
      break;

    default:
      $img = 'fas fa-question-circle';
      break;
  }

  return $img;
}

/**
 * Get online docs viewer supported files extensions
 * @return array
 */
function utils_getOnlineViewerExts()
{
  return array
  (
    'ai',
    'doc',
    'docx',
    'dxf',
    'pdf',
    'ppt',
    'pptx',
    'psd',
    'rar',
    'xls',
    'xlsx',
    'xps',
  );
}

/**
 * Get image files extensions
 * @return array
 */
function utils_getImageExts()
{
  return array
  (
    'ico',
    'gif',
    'jpg',
    'jpeg',
    'jpc',
    'jp2',
    'jpx',
    'xbm',
    'wbmp',
    'png',
    'bmp',
    'tif',
    'tiff',
    'psd',
    'svg',
  );
}

function utils_getThumbnailExts()
{
  return array
  (
    'gif',
    'jpg',
    'jpeg',
    'xbm',
    'wbmp',
    'png',
    'bmp',
    'svg',
  );
}

/**
 * Get video files extensions
 * @return array
 */
function utils_getVideoExts()
{
  return array
  (
    '3gp',
    '3g2',
    'avi',
    'f3v',
    'flv',
    'm4v',
    'mkv',
    'mov',
    'mov',
    'mp4',
    'mpeg',
    'ogm',
    'ogv',
    'webm',
  );
}

/**
 * Get audio files extensions
 * @return array
 */
function utils_getAudioExts()
{
  return array
  (
    'aac',
    'flac',
    'm4a',
    'mp3',
    'ogg',
    'wav',
    'wma',
  );
}

/**
 * Get text file extensions
 * @return array
 */
function utils_getTextExts()
{
  return array
  (
    'bat',
    'bash',
    'c',
    'c++',
    'cc',
    'conf',
    'config',
    'cpp',
    'cs',
    'csh',
    'css',
    'csv',
    'cue',
    'dtd',
    'eml',
    'ftpquota',
    'gitignore',
    'h',
    'hpp',
    'htaccess',
    'htm',
    'html',
    'html5',
    'ini',
    'ino',
    'java',
    'js',
    'jsp',
    'json',
    'lua',
    'less',
    'lock',
    'log',
    'm3u',
    'm3u8',
    'mak',
    'map',
    'md',
    'msg',
    'passwd',
    'php',
    'php4',
    'php5',
    'phps',
    'phtml',
    'pls',
    'py',
    'r',
    'resx',
    'rpy',
    'sass',
    'scss',
    'sh',
    'shtml',
    'sql',
    'svg',
    'tpl',
    'ts',
    'twig',
    'txt',
    'vba',
    'xhtml',
    'xml',
    'xsl',
    'yaml',
    'yml',
  );
}

/** Get archive file extensions
 * @return array
 */
function utils_getArchiveExts()
{
  return array
  (
    'bz', // few used
    'bz2',
    'gz',
    'tar',
    'tbz2',
    'tgz',
    'zip',
  );
}

/**
 * Get file names of text files w/o extensions
 * @return array
 */
function utils_getTextNames()
{
  return array
  (
    'license',
    'readme',
    'authors',
    'contributors',
    'changelog',
  );
}

/**
 * Get mime types of text files
 * @return array
 */
function utils_getTextExtraMimes()
{
  return array
  (
    'application/x-empty',
    'text/plain',
    'image/svg+xml',
    'message/rfc822',
  );
}

/**
 * Get mime type
 * @param string $file_path
 * @return mixed|string
 */
function utils_getMimeType($file_path)
{
  if (function_exists('finfo_open'))
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    return $mime;
  }
  elseif (function_exists('mime_content_type'))
  {
    return mime_content_type($file_path);
  }
  elseif (!stristr(ini_get('disable_functions'), 'shell_exec'))
  {
    $file = escapeshellarg($file_path);
    $mime = shell_exec('file -bi ' . $file);
    return $mime;
  }
  else
  {
    return '--';
  }
}

function utils_convertPathToSys($path)
{
  $path = utils_cleanPath($path);
  if (DIRECTORY_SEPARATOR != '/')
  {
    $path = str_replace(array('/'), DIRECTORY_SEPARATOR, $path);
  }
  return $path;
}

function utils_convertSysToPath($rootpath, $syspath)
{
  $rootpath = realpath($rootpath);
  if (substr($syspath, 0, strlen($rootpath)) === $rootpath)
  {
    $syspath = substr($syspath, strlen($rootpath));
  }
  if (DIRECTORY_SEPARATOR != '/')
  {
    $syspath = str_replace(DIRECTORY_SEPARATOR, '/', $syspath);
  }
  return utils_cleanPath($syspath);
}

function utils_isPathIncludeInto($root, $child, $notMatchingIfSame = false)
{
  if (!$root) $root = '/';
  if (!$child) $child = '/';
  $root = utils_cleanPath($root);
  $child = utils_cleanPath($child);
  if ($notMatchingIfSame)
  {
    $notMatchingIfSame = ($root == $child);
  }
  return !$notMatchingIfSame && substr($child, 0, strlen($root)) == $root;
}

/**
 * Get recursive folder size
 * limitSize is used to stop if size reach the limit, avoiding uselss computation
 * knownInodes avoid recursive link loop
 */
function utils_folderSize($path, int $limitSize = 0x7FFFFFFF, array &$knownInodes = array())
{
    $totalSize = 0;
  $path = utils_convertPathToSys($path);

  $inode = fileinode (realpath($path));
  if ($inode && in_array($inode, $knownInodes))
  {
    $inode = false;
  }
  elseif (!is_dir($path))
  {
    $totalSize += filesize($path);
    $knownInodes[] = $inode;
  }
  else
  {
      $files = scandir($path);

    foreach($files as $name)
    {
      if ($totalSize > $limitSize) break;
      if ($name == '.' || $name == '..') continue;

      $totalSize += utils_folderSize ($path . DIRECTORY_SEPARATOR . $name, $limitSize - $totalSize, $knownInodes);
    }
  }

  if ($inode)
  {
    $knownInodes[] = $inode;
  }
    return $totalSize;
}

?>
