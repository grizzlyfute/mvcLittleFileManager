<?php
$title = $tr->translate('file.info');
$path = $VIEWVARS['file']->getFullPath();
include ('views/headers.php'); ?>
<link rel="stylesheet" href="libs/highlight-11.2.0/styles/<?php echo $CONFIG['highlightjs_style'] ?>.min.css"/>
<script src="libs/highlight-11.2.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
<div class="container-fluid mb-2" style="margin:0em; padding:1em;height:80vh;">
	<div class="row">
<?php
	$filetype = $VIEWVARS['type'];
	$file = $VIEWVARS['file'];
?>
		<div class="d-inline-flex">
		<?php
			/*  hide text on mobile
			https://stackoverflow.com/questions/14207109/hiding-elements-in-responsive-layout
			| 	                |   XS    |    SM   |    MD   |   LG    |   XL    |
			| d-none d-sm-block | hidden  | visible | visible | visible | visible |
			| d-none d-md-block | hidden  | hidden  | visible | visible | visible |
			| d-none d-lg-block | hidden  | hidden  | hidden  | visible | visible |
			| d-none d-xl-block | hidden  | hidden  | hidden  | hidden  | visible |
			| d-block d-sm-none | visible | hidden  | hidden  | hidden  | hidden  |
			| d-block d-md-none | visible | visible | hidden  | hidden  | hidden  |
			| d-block d-lg-none | visible | visible | visible | hidden  | hidden  |
			| d-block d-xl-none | visible | visible | visible | visible | hidden  |
			 */ ?>

			<div class="d-none d-sm-block">
				<a href="?p=<?php echo $VIEWVARS['parent'] ?>">
					<i class="fas fa-chevron-circle-left go-back"></i>&nbsp;<?php $tr->trans('common.back'); ?>
				</a> &nbsp;
			</div>
			<div class="d-block d-sm-none">
				<a href="?p=<?php echo $VIEWVARS['parent']?>" title="<?php $tr->trans('common.back'); ?>">
					<i class="fas fa-chevron-circle-left go-back" style="font-size:1.3em"></i>&nbsp;
				</a> &nbsp;
			</div>

			<?php if ($VIEWVARS['previous'] != null): ?>
			<div class="d-none d-sm-block">
				<a href="?action=view&p=<?php echo rawurlencode($VIEWVARS['previous']->getFullPath())?>" title="<?php echo utils_pathToHtml($VIEWVARS['previous']->getName()) ?>">
					<i class="fas fa-backward"></i>&nbsp;<?php $tr->trans('file.previous')?>
				</a>&nbsp;
			</div>
			<div class="d-block d-sm-none">
				<a href="?action=view&p=<?php echo rawurlencode($VIEWVARS['previous']->getFullPath())?>" title="<?php echo utils_pathToHtml($VIEWVARS['previous']->getName()) ?>">
					<i class="fas fa-backward" style="font-size:1.3em"></i>&nbsp;
				</a>&nbsp;
			</div>
			<?php endif; ?>
			<?php if ($VIEWVARS['next'] != null): ?>
			<div class="d-none d-sm-block">
				<a href="?action=view&p=<?php echo rawurlencode($VIEWVARS['next']->getFullPath())?>" title="<?php echo utils_pathToHtml($VIEWVARS['next']->getName()) ?>">
					<i class="fas fa-forward"></i>&nbsp;<?php $tr->trans('file.next')?>
				</a>&nbsp;
			</div>
			<div class="d-block d-sm-none">
				<a href="?action=view&p=<?php echo rawurlencode($VIEWVARS['next']->getFullPath())?>" title="<?php echo utils_pathToHtml($VIEWVARS['next']->getName()) ?>">
					<i class="fas fa-forward" style="font-size:1.3em"></i>&nbsp;
				</a>&nbsp;
			</div>
			<?php endif; ?>

			<?php
			{
				$filename = utils_pathToHtml($file->getName());
				$filesize = utils_getFileSizeSuffix($file->getSize());
				$mimetype = isset ($VIEWVARS['mimetype']) ? ' (' . $VIEWVARS['mimetype'] . ')' : '';
			?>
			<div class="d-none d-sm-block">
				<a href="?action=download&p=<?php echo rawurlencode($file->getFullPath()) ?>&dlf" title="<?php echo $filename . ' - ' . $filesize . $mimetype?>" >
					<i class="fas fa-cloud-download-alt"></i>&nbsp;<?php $tr->trans('file.download') ?>
				</a>&nbsp;
			</div>
			<div class="d-block d-sm-none">
				<a href="?action=download&p=<?php echo rawurlencode($file->getFullPath()) ?>&dlf" title="<?php echo $filename . ' - ' . $filesize . $mimetype?>" >
					<i class="fas fa-cloud-download-alt" style="font-size:1.3em"></i>&nbsp;
				</a>&nbsp;
			</div>
			<?php } ?>

			<?php if ($filetype == 'image' && $CONFIG['thumbnail'] && function_exists('gd_info')): ?>
			<div class="d-none d-sm-block">
				<a href="?action=imagesview&p=<?php echo $file->getFullPath(); ?>">
					<i class="fas fa-table"></i>&nbsp;<?php $tr->trans('file.imagesviewview') ?>
				</a>&nbsp;
			</div>
			<div class="d-block d-sm-none">
				<a href="?action=imagesview&p=<?php echo $file->getFullPath(); ?>" title="<?php $tr->trans('file.imagesviewview'); ?>">
					<i class="fas fa-table" style="font-size:1.3em"></i>&nbsp;
				</a>&nbsp;
			</div>
			<?php endif; ?>
			<?php if ($filetype == 'text' && $perm->isGranted(Permission::MODIFY, $path)): ?>
			<!-- TODO $$$$$ -->
			<div class="d-none d-sm-block">
				<a href="?action=edit&p=<?php echo rawurlencode($file->getFullPath()) ?>" class="edit-file">
					<i class="fas fa-pen-square"></i>&nbsp;<?php $tr->trans('file.edit') ?>
				</a>
			</div>
			<div class="d-block d-sm-none">
				<a href="?action=edit&p=<?php echo rawurlencode($file->getFullPath()) ?>" class="edit-file" title="<?php $tr->trans('file.edit'); ?>">
					<i class="fas fa-pen-square" style="font-size:1.3em"></i>&nbsp;
				</a> &nbsp;
			</div>
			<?php endif; ?>
		</div>

		<div class="container container-fluid" style="max-height:100vh;">
		<?php
		$file_url = '?action=download&p=' . rawurlencode($file->getFullPath());
		if ($VIEWVARS['online'] == 'google')
		{
			$file_url = $_SERVER['SERVER_NAME'] . $file_url;
			echo '<iframe src="https://docs.google.com/viewer?embedded=true&hl=en&url=' . $file_url . '" frameborder="no" style="width:100%;min-height:460px"></iframe>';
		}
		else if ($VIEWVARS['online'] == 'microsoft')
		{
			$file_url = $_SERVER['SERVER_NAME'] . $file_url;
			echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=' . $file_url . '" frameborder="no" style="width:100%;min-height:460px"></iframe>';
		}
		else if ($filetype == 'archive')
		{
			echo '<p class="break-word">' . PHP_EOL;
			if ($VIEWVARS['archive_totalfiles'] > 0)
			{
				echo '<p>' . PHP_EOL;
				echo '	<b>' . $tr->translate('file.archivefilescount') . '</b>: ' . $VIEWVARS['archive_totalfiles']. '<br>' . PHP_EOL;
				echo '	<b>' . $tr->translate('file.archivetotalsize') . '</b>: ' . utils_getFileSizeSuffix($VIEWVARS['archive_totalsize']). '<br>' . PHP_EOL;
				echo '	<b>' . $tr->translate('file.archivesizeinarchive') . '</b>: ' . utils_getFileSizeSuffix($VIEWVARS['archive_sizeinarchive']) . '<br>' . PHP_EOL;
				echo '	<b>' . $tr->translate('file.archiveratio') . '</b>: ' . $VIEWVARS['archive_ratio']. '%<br>' . PHP_EOL;
				echo '</p>' . PHP_EOL;

				if ($VIEWVARS['archive_filenames'] !== false)
				{
					echo '<code class="maxheight">';
					foreach ($VIEWVARS['archive_filenames'] as $fn)
					{
						if ($fn['folder'])
						{
							echo '<b>' . htmlspecialchars($fn['name']) . '</b><br/>';
						}
						else
						{
							echo htmlspecialchars($fn['name']) . ' (' . utils_getFileSizeSuffix($fn['filesize']) . ')<br/>';
						}
					}
					echo '</code>' . PHP_EOL;
				}
				else
				{
					$tr->trans('file.archivenofileerror');
				}
			}
			else
			{
				$tr->trans('file.archivenofileerror');
			}
			echo '</p>' . PHP_EOL;
		}
		elseif ($filetype == 'pdf')
		{
			echo '<object data="' . $file_url . '" type="application/pdf" style="width:100%; min-height:80vh;" class="position-relative">' . PHP_EOL;
			echo '	<embed src="' . $file_url . '" type="application/pdf" />' . PHP_EOL;
			echo '</object>' . PHP_EOL;

			// "the min-height 60% vh is needed to filling the page, don't know why
			// echo '<iframe style="min-height:60vh;" src="' . $file_url . '" width="100%" height="100%" scrolling="no" frameBorder="0"></iframe>' . PHP_EOL;
		}
		elseif ($filetype == 'image')
		{
			echo '<img src="' . $file_url . '" alt="" class="preview-img" style="max-width:100%;max-height:100%">' . PHP_EOL;
		}
		elseif ($filetype == 'audio')
		{
			echo '<audio src="' . $file_url . '" controls preload="metadata"></audio>' . PHP_EOL;
		}
		elseif ($filetype == 'video')
		{
			echo '<div class="preview-video"><video src="' . $file_url . '" width="640" height="360" controls preload="metadata"></video></div>' . PHP_EOL;
		}
		elseif ($filetype == 'text')
		{
			$syspath = $CONFIG['rootdirectory'] . DIRECTORY_SEPARATOR . utils_convertPathToSys($file->getFullPath());
			$content = file_get_contents($syspath);
			$ext = strtolower(pathinfo($syspath, PATHINFO_EXTENSION));
			// Highlight
			$hljs_classes = array
			(
				'shtml' => 'xml',
				'htaccess' => 'apache',
				'phtml' => 'php',
				'lock' => 'json',
				'svg' => 'xml',
			);
			$hljs_class = isset($hljs_classes[$ext]) ? 'lang-' . $hljs_classes[$ext] : 'lang-' . $ext;
			if (empty($ext) || in_array(strtolower($file->getName()), utils_getTextNames()) || preg_match('#\.min\.(css|js)$#i', $file->getName()))
			{
				$hljs_class = 'nohighlight';
			}
			echo '<pre class="with-hljs"><code class="' . $hljs_class . '">' . htmlspecialchars($content) . '</code></pre>';
		}
		else
		{
			echo '<div class="center m-1" style="max-width:100%; max-height:100%; float: none">' . PHP_EOL;
			echo '	<a style="color: #222222" href="' . $file_url . '">' . PHP_EOL;
			echo '		<p><i class="' . utils_getFileIconClass($file->getName()) . ' fa-10x"></i></p>' . PHP_EOL;
			echo '		<p>' . $file->getName() . '</p>' . PHP_EOL;
			echo '	</a>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
		}
		?>
		</div>

	</div>
</div>
<?php include ('views/footers.php'); ?>
