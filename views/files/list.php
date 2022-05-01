<?php
// No requiere_include once: we are in a function
$title = $CONFIG['apptitle'];
$path = $VIEWVARS['curpath'];
include ('views/headers.php');?>
<script>

function removeUserRoot(filePath)
{
	var userRootPath = "<?php echo $this->getCurrentUser() != null ? $this->getCurrentUser()->getRootDirectory() : '/' ?>";
	if (!filePath.startsWith("/")) filePath = "/" + filePath;
	if (filePath.startsWith(userRootPath))
		filePath = "/" + filePath.substr(userRootPath.length);
	return filePath;
}

function fileActionPreparePre(filepath, action)
{
	var re = /^\/+|\/+$/g;
	var suggestPath = removeUserRoot("<?php echo $path?>");
	suggestPath = suggestPath.replace(re, "");
	suggestPath = "/" + suggestPath;
	var fpSplit = filepath.split('; ');
	var suggestName = null;
	if (fpSplit.length == 1) suggestName = fpSplit[0];
	else suggestName = fpSplit.pop().replace(/\..+$/, "");
	suggestName = suggestName.replace(re, "");

	$("#fileActionSrcPath").val(filepath);
	$("#fileAction").val(action);

	$("#fileActionDstDiv").hide();
	$("#fileActionCompressMethod").hide();
	switch (action)
	{
		case "copy":
			$("#fileActionModalLabel").text("<?php $tr->trans('file.copy')?>");
			$("#fileActionDstPath").show();
			$("#fileActionDstPath").val(suggestPath + "/copy_" + suggestName);
			$("#fileActionDstDiv").show();
			break;
		case "moverename":
			$("#fileActionModalLabel").text("<?php $tr->trans('file.moverename')?>");
			$("#fileActionDstPath").show();
			$("#fileActionDstPath").val(suggestPath + "/" + suggestName);
			$("#fileActionDstDiv").show();
			break;
		case "delete":
			$("#fileActionModalLabel").text("<?php $tr->trans('file.delete')?>");
			break;
		case "compress":
			$("#fileActionModalLabel").text("<?php $tr->trans('file.compress')?>");
			$("#fileActionDstPath").show();
			$("#fileActionDstPath").val(suggestPath + "/" + suggestName + ".xxx");
			$("#fileActionCompressMethod").show();
			$("#fileActionDstDiv").show();
			break;
		case "extract":
			$("#fileActionModalLabel").text("<?php $tr->trans('file.extract')?>");
			$("#fileActionDstPath").show();
			$("#fileActionDstPath").val(suggestPath);
			$("#fileActionDstDiv").show();
			break;
		case "downloadzip":
			$("#fileActionForm").submit();
			break;
		default:
			$("#fileActionModalLabel").text('');
			$('#fileActionForm > button[type=submit]').prop('disabled', true);
			break;
	}
}

function fileActionPreparePreMultiple(action)
{
	var files = [];
	var filepaths = $("input[name='chkitems[]']:checked");
	filepaths.each(function()
	{
		files.push(removeUserRoot($(this).val()));
	});
	fileActionPreparePre(files.join("; "), action);
}

function change_checkboxes(e, t)
{
	for (var n = e.length - 1; n >= 0; n--)
	{
		if (typeof t == "boolean")
		{
			e[n].checked = t;
		}
		else
		{
			e[n].checked = !e[n].checked;
		}
	}
	on_checkbox_changed(null);
}
function get_checkboxes()
{
	var e = document.getElementsByName("chkitems[]");
	var t = [];
	for (var n = e.length - 1; n >= 0; n--)
	{
		if (e[n].type == "checkbox")
			t.push(e[n]);
	}
	return t;
}
function select_all()
{
	change_checkboxes(get_checkboxes(), true);
}
function unselect_all()
{
	change_checkboxes(get_checkboxes(), false);
}
function invert_all()
{
	change_checkboxes(get_checkboxes());
}
function checkbox_toggle()
{
	var e = get_checkboxes();
	e.push(this);
	change_checkboxes(e, !this.checked);
}
function on_checkbox_changed(checkBoxEltUnused)
{
	var e = get_checkboxes();
	var haschecked = e.find(x => x.checked) != undefined;
<?php if (class_exists('ZipArchive')): ?>
	if (haschecked) $("#multipleDownload").show();
	else $("#multipleDownload").hide();
<?php endif; ?>

	<?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
	if (haschecked)
	{
		var isarchive = true;
		var a_archive = <?php echo json_encode (utils_getArchiveExts()) ?>;
		for (var n = e.length - 1; n >= 0; n--)
		{
			if (e[n].checked)
			{
				if (isarchive && a_archive.find(x => x == e[n].value.substring(e[n].value.lastIndexOf('.')+1)))
				{
					isarchive = true;
				}
				else if (isarchive)
				{
					isarchive = false;
				}
			}
		}

		$("#multipleCopy").show();
		$("#multipleMoverename").show();
		$("#multipleDelete").show();
		$("#multipleCompress").show();
		if (isarchive) $("#multipleExtract").show();
		else $("#multipleExtract").hide();
	}
	else
	{
		$("#multipleCopy").hide();
		$("#multipleMoverename").hide();
		$("#multipleDelete").hide();
		$("#multipleCompress").hide();
		$("#multipleExtract").hide();
	}
	<?php endif; ?>
}

//Upload files using URL @param {Object}
function upload_from_url($this)
{
	let form = $($this), resultWrapper = $("div#js-url-upload__list");
	$.ajax
	({
		type: form.attr('method'),
		url: form.attr('action'),
		data: form.serialize()+"&ajax="+true,
		beforeSend: function()
		{
			form.find("input[name=uploadurl]").attr("disabled","disabled");
			form.find("button").hide();
		},
		success: function (data)
		{
			if(data)
			{
				data = JSON.parse(data);
				if(data.done)
				{
					resultWrapper.append("<div class=\"alert alert-success row\"><?php $tr->trans('common.success')?>: " + data.done.name + "</div>");
					form.find("input[name=uploadurl]").val('');
				}
				else if(data['fail'])
				{
					resultWrapper.append('<div class="alert alert-danger row"><?php $tr->trans('common.error')?>: '+
						data.fail.message+'</div>');
				}
				form.find("input[name=uploadurl]").removeAttr("disabled");form.find("button").show();
			}
		},
		error: function(xhr)
		{
			form.find("input[name=uploadurl]").removeAttr("disabled");
			form.find("button").show();console.error(xhr);
		}
	});
	return false;
}

function escapeHtml(str)
{
	return str
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&apos;");
}

function selectDstDir(rootPath, toggle)
{
	var selectDstDirDiv = $('#selectDstDirDiv');
	var userRootPath = "<?php echo $this->getCurrentUser() != null ? $this->getCurrentUser()->getRootDirectory() : '/' ?>";
	if (toggle) selectDstDirDiv.toggle();
	else $("#fileActionDstPath").val(rootPath);
	if (!selectDstDirDiv.is(":visible")) return;
	$.ajax
	({
		type: "GET",
		url: "?action=lsdirjson&p=" + encodeURIComponent(userRootPath + "/" + rootPath),
		success: function (data)
		{
			if (data)
			{
				data = JSON.parse(data);
				var splitted = rootPath.split("/");
				var last = splitted.pop();
				$("#selectDstDirParent").html
				(
					"<a href=\"javascript:void()\" role=\"button\" onClick=\"selectDstDir('" + (splitted.length > 0 ? splitted.join("/") : "/") + "', false)\"\n" +
					"	title=\"<?php $tr->trans('common.back')?>\">\n" +
					"	<i class=\"fas fa-folder\"></i>&nbsp;" + last + "\n" +
					"</a>\n"
				);
				var htmlStr = "";
				for (entry of data)
				{
					entry = escapeHtml(entry);
					htmlStr	+=
					"<li>\n" +
					"	<a href=\"javascript:void()\" role=\"button\" onClick=\"selectDstDir('" + rootPath + "/" + entry + "', false)\"" +
					"	   title=\"" + rootPath + "/" + entry + "\">\n" +
					"		<i class=\"fas fa-folder\"></i>&nbsp;" + entry + "</i>\n" +
					"	</a>\n" +
					"</li>\n";
				};
				$("#selectDstDirChildren").html(htmlStr);
			}
		},
		error: function(xhr)
		{
			// JSON.stringify(xhr);
			console.error ("Error: " + xhr.status + " - " + xhr.statusText);
		}
	});
}

// Dom Ready Event
$(document).ready( function ()
{
	// DataTable init
	var mainTable = $('#main-table');
	var tableLng = mainTable.find('th').length;
	var _targets = (tableLng && tableLng == 7) ? [0,4,5,6] : tableLng == 5 ? [0,4] : [];
	mainTable = mainTable.DataTable(
	{
		"paging": false,
			"info": false,
			"columnDefs": [{"targets": _targets, "orderable": false, "bsortable": false}],
			"responsive": true,
			// "lengthChange": false,
			// "bPaginate": false,
			// ,"order": [[1, "desc"]]
			// "searching": false,
			// "pageLength": 20,
	});
	$('#search-addon').on( 'keyup', function ()
	{ // Search using custom input box
		mainTable.search( this.value ).draw();
	});
});

</script>
<div id="wrapper" class="container-fluid">
	<?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
	<!-- New Item creation -->
	<div id="createNewItemModal" class="modal fade" tabindex="-1" role="dialog" aria-label="newItemModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="newItemModalLabel"><i class="fas fa-plus-square"></i>&nbsp;<?php $tr->trans('file.newitem') ?></h5>
					<button type="button" class="btn-close rounded" title="<?php $tr->trans('common.close')?>" data-bs-dismiss="modal" aria-label="<?php $tr->trans('common.close')?>"></button>
				</div>
				<div class="modal-body">
				<form id="newItemForm" action="?action=newitem" method="POST">
					<input type="hidden" id="parent" name="parent" value="<?php echo utils_pathToHtml($path) ?>"/>
					<p><label for="newfile"><?php $tr->trans('file.itemtype')?></label></p>

						<div class="form-radio form-check-inline">
							<input type="radio" id="radio_newfile" name="itemtype" value="file" class="form-check-radio" checked/>
							<label class="form-check-label" for="customRadioInline1"><?php $tr->trans('file.newfile') ?></label>
						</div>

						<div class="form-radio form-check-inline">
							<input type="radio" id="radio_newfolder" name="itemtype" value="folder" class="form-check-radio"/>
							<label class="form-check-label" for="customRadioInline2"><?php $tr->trans('file.newfolder') ?></label>
						</div>

						<p class="mt-3"><label for="newitemname"><?php $tr->trans('file.itemname') ?></label></p>
						<input type="text" name="newitemname" id="newitemname" value="" class="form-control"/>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-primary rounded" data-bs-dismiss="modal">
							<i class="fas fa-times-circle"></i>&nbsp;<?php $tr->trans('common.cancel') ?>
						</button>
						<button type="submit" class="btn btn-success rounded">
							<i class="fas fa-check-circle"></i>&nbsp;<?php $tr->trans('common.ok')?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- fileAction -->
	<div id="fileActionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fileActionModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="fileActionForm" method="POST" action="?action=fileaction">
					<input type="hidden" name="parent" value="<?php echo utils_pathToHtml($path) ?>"/>
				<div class="modal-header">
				<h3 id="fileActionModalLabel">#</h3>
					<button type="button" class="btn-close rounded" title="<?php $tr->trans('common.close')?>" data-bs-dismiss="modal" aria-hidden="true"></button>
				</div> <!-- modal-header -->
				<div class="modal-body">

					<input type="hidden" id="fileAction" name="fileAction" value="" />

					<div class="container">
						<div id="fileActionSrcDiv" class="row">
							<div class="col-sm-4">
								<label for="fileActionSrcPath"><strong><?php $tr->trans('file.source')?></strong></label>
							</div>
							<div class="col-sm-8">
								<input type="text" class="form-control rounded"
									id="fileActionSrcPath" name="fileActionSrcPath" value=""
									readonly/>
							</div>
						</div>

						<div id="fileActionDstDiv" class="row" style="display:none;">
							<div class="col-sm-3">
								<label for="fileActionDstPath"><strong><?php $tr->trans('file.destination')?></strong></label>
							</div>
							<div class="col-sm-1">
								<button type="button" id="btnSelectDstDir" class="rounded" onClick="selectDstDir(removeUserRoot('<?php echo utils_pathToHtml($path)?>'), true);"
										title="<?php $tr->trans('file.destination')?>...">...</button>
							</div>
							<div class="col-sm-8">
								<input type="text" class="form-control rounded"
									id="fileActionDstPath" name="fileActionDstPath" value=""/>
							</div>
						</div>

						<div id="selectDstDirDiv" class="row" style="display:none;">
							<ul style="list-style: none;">
							<li id="selectDstDirParent">
								<!-- js filled-->
							</li>
							<li>
								<ul style="list-style: none;" id="selectDstDirChildren">
									<li><i class="fas fa-folder"></i>&nbsp;<!-- js filled--></li>
								</ul>
							</li>
							</ul>
						</div>
					</div>
						<div id="fileActionCompressMethod" class="row" style="display:none;">
						<hr/>
						<div class="col-sm-4">
							<label for="compressAction"><strong>-&gt;</strong></label>
						</div>
						<div class="col-sm-8">
							<select id="compressAction" class="form-select rounded" name="compressAction" >
							<option selected>--</option>
						<?php if (class_exists('ZipArchive')): ?>
							<option value="zip">Zip</option>
						<?php endif; ?>

						<?php if (class_exists('PharData')): ?>
							<option value="tar">Tar</option>
							<option value="targz">Tgz</option>
							<option value="tarbz2">Tbz2</option>
						<?php endif; ?>
							</select>
						</div>
					</div> <!-- fileActionCompressMethod -->

				</div> <!-- modal-body -->
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary close rounded" title="<?php $tr->trans('common.close')?>" data-bs-dismiss="modal">
						<i class="fas fa-times-circle"></i>&nbsp;<?php $tr->trans('common.close')?>
					</button>
					<button class="btn btn-success float-end rounded" type="submit">
						<i class="fas fa-check-circle"></i>&nbsp;<?php $tr->trans('common.ok') ?>
					</button>
				</div> <!-- modal-footer -->
				</form>
			</div> <!-- modal-content -->
		</div> <!-- modal-dialog -->
	</div> <!-- FileActionModal -->
</div> <!-- wrapper -->

<?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
<link href="libs/dropzone-5.7.0/dropzone.min.css" rel="stylesheet"/>
<div id="wrapper" class="container-fluid">
	<div id="uploadModal" class="modal fade" tabindex="-1" role="dialog" aria-label="uploadModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="uploadModalLabel"><i class="fas fa-plus-square"></i>&nbsp;<?php $tr->trans('file.upload') ?></h5>
					<button type="button" class="btn-close " title="<?php $tr->trans('common.close')?>" data-bs-dismiss="modal" aria-label="<?php $tr->trans('common.close')?>"></button>
				</div> <!-- modal-header -->
				<div class="modal-body">
					<div class="mb-2">
						<ul class="nav nav-tabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="fileUploadTab" data-bs-toggle="tab" data-bs-target="#fileUploader" type="button" role="tab" aria-controls="fileUploader" aria-selected="true">
									<i class="fas fa-arrow-circle-up"></i>&nbsp;<?php echo $tr->trans('file.upload') ?>
								</button>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="urlUploadTab" data-bs-toggle="tab" data-bs-target="#urlUploader" type="button" role="tab" aria-controls="urlUploader" aria-selected="false">
									<i class="fas fa-link"></i>&nbsp;<?php $tr->trans('file.uploadfromurl')?>
								</button>
							</li>
						</ul>
					</div>
					<div class="tab-content">
						<p>
							<?php echo $tr->trans('file.destination') ?>: <?php echo $path ?>
						</p>

						<div class="tab-pane fade show active" id="fileUploader" role="tabpanel" aria-labelledby="fileUploadTab">
							<form id="js-form-uploadfile" method="POST" action="?action=upload" class="form-inline dropzone" enctype="multipart/form-data">
								<input type="hidden" name="p" value="<?php echo $path ?>"/>
								<div class="fallback">
									<input name="file" type="file" multiple/>
								</div>
							</form>
						</div>

						<div class="tab-pane fade upload-url-wrapper" id="urlUploader" aria-labelledby="urlUploadTab">
							<form id="js-form-url-upload" class="form-inline" onsubmit="return upload_from_url(this);" method="POST" action="?action=upload">
								<input type="hidden" name="p" value="<?php echo $path ?>"/>
								<input type="url" placeholder="URL" name="uploadurl" required class="form-control" style="width: 80%">
								<button type="submit" class="btn btn-primary ml-3 mt-2">
									<?php echo $tr->trans('file.upload') ?>
								</button>
							</form>
							<div id="js-url-upload__list" class="col-9 mt-3"></div>
						</div>
					</div> <!-- tab-content -->
				</div> <!-- modal-body -->
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary rounded" title="<?php $tr->trans('common.close')?>" data-bs-dismiss="modal" onClick="window.location.reload(true)">
						<?php $tr->trans('common.close')?>
					</button>
				</div> <!-- modal-footer -->
			</div> <!-- modal-content -->
		</div> <!-- modal-dialog -->
	</div> <!-- uploadModal -->
</div> <!-- wrapper -->

<script src="libs/dropzone-5.7.0/dropzone.min.js"></script>
<script>
Dropzone.options.jsFormUploadfile =
{
	timeout: 600000,
	maxFilesize: <?php echo $CONFIG['maxuploadsize']; ?>,
	parallelUploads: 2,
	createImageThumbnails: true,
	init: function ()
	{
		this.on("sending", function (file, xhr, formData)
		{
			// let _path = (file.fullPath) ? file.fullPath : file.name;
			// document.getElementById("filename").value = file.name;
			xhr.ontimeout = (function()
			{
				alert('Error: Server Timeout');
			});
		}).on("success", function (res)
		{
			console.log("success");
			// console.log('Upload Status >> ', res.status);
		}).on("error", function(file, response)
		{
			console.log("error");
			$(file.previewElement).find('.dz-error-message').html(response);
			alert(response);
		});
	}
}
</script>
<?php endif; ?>

<?php
$num_files = 0;
$num_folders = 0;
$all_files_size = 0;
?>
<div class="table-responsive" style="overflow-x: visible; overflow-y: visible">
	<table class="table table-bordered table-hover table-sm bg-white" id="main-table" name="main-table">
		<thead class="thead-white">
		<tr>
			<th style="width:3%" class="form-checkbox-header">
				<div class="form-check form-checkbox">
					<input type="checkbox" class="form-check-input" id="js-select-all-items" onclick="checkbox_toggle()"/>
					<label class="form-check-label " for="js-select-all-items"></label>
				</div>
			</th>
			<th><?php $tr->trans('file.name') ?></th>
			<th><?php $tr->trans('file.size') ?></th>
			<th><?php $tr->trans('file.modificationdate') ?></th>
			<?php if ($perm->isGranted(Permission::CHANGEPERMS, $path)): ?>
			<th><?php $tr->trans('file.permissions') ?></th>
			<th><?php $tr->trans('file.owner') ?></th>
			<?php endif; ?>
			<th><?php $tr->trans('file.actions') ?></th>
		</tr>
		</thead>
		<tbody>
<?php // link to parent folder
			if ($VIEWVARS['parent'] !== false &&
				($path != '/' && $path != '' &&
				($this->getCurrentUser() == null ||
				utils_isPathIncludeInto($this->getCurrentUser()->getRootDirectory(), $VIEWVARS['parent'], false)))): ?>
		<tr>
			<td></td>
			<td>
				<a href="?action=ls&p=<?php echo rawurlencode($VIEWVARS['parent']) ?>"
					title="<?php $tr->trans('common.back')?>"
					style="margin-right:1em;">
					<i class="fas fa-chevron-circle-left go-back"></i>&nbsp;..
				</a>
			</td>
			<td></td>
			<td></td>
			<?php if ($perm->isGranted(Permission::CHANGEPERMS, $path)): ?>
			<td></td>
			<td></td>
			<?php endif; ?>
			<td></td>
		</tr>
<?php endif; ?>

<?php
$index_gen = 1;
$thumbnailExts = utils_getThumbnailExts();
foreach ($VIEWVARS['items'] as $item)
{
	if ($item->getIsDir())
	{
		if ($item->getIsLink()) $img = 'fas fa-link';
		else $img = 'fas fa-folder';
		$itemlink = '?action=ls&p=' . rawurlencode($item->getFullPath());
	}
	else
	{
		$itemlink = '?action=view&p=' . rawurlencode($item->getFullPath());
		if ($item->getIsLink()) $img = 'fas fa-link';
		else $img = utils_getFileIconClass($item->getName());
	}
?>
			<tr>
				<td class="form-checkbox-td">
				<div class="form-check form-checkbox">
					<input type="checkbox"
						   class="form-check-input"
						   id="chk_<?php echo $index_gen ?>"
                           name="chkitems[]"
						   value="<?php echo utils_pathToHtml($item->getFullPath()) ?>"
						   onClick="on_checkbox_changed(this);"/>
					<label class="form-check-label" for="chk_<?php echo $index_gen ?>"></label>
				</div>
				</td>
				<td>
					<div class="filenamediv">
						<a href="<?php echo $itemlink ?>">
							<?php if ($CONFIG['thumbnail'] && function_exists('gd_info') && in_array($item->getExtension(), $thumbnailExts)) : ?>
							<img src="?action=thumbnail&p=<?php echo rawurlencode($item->getFullPath()); ?>" alt="" style="height:2em"/>
							<?php else: ?>
							<i class="<?php echo $img ?>"></i>&nbsp;
							<?php endif; ?>
							<?php echo utils_pathToHtml($item->getName()) ?>
						</a>
						<?php
							if ($item->getIsLink())
							{
								$link = readlink($CONFIG['rootdirectory'] . DIRECTORY_SEPARATOR . utils_convertPathToSys($item->getFullPath()));
								if ($link)
								{
									$root = $CONFIG['rootdirectory'];
									if ($this->getCurrentUser())
									{
										if ($this->getCurrentUser()->getRootDirectory() != '/')
										{
											$root .= utils_convertPathToSys('/' . $this->getCurrentUser()->getRootDirectory());
										}
									}
									$root = utils_cleanPath($root);
									if (substr($link, 0, 1) != '/')
									{
										$link = $root . '/' . $link;
									}
									$link = utils_cleanPath($link);
									if (utils_isPathIncludeInto($root, $link))
									{
										$link = substr($link, strlen($root));
										echo ' &rarr; <i>' . $link . '</i>';
									}
								}
							}
						?>
					</div>
				</td>
				<td><?php
					if (!$item->getIsDir())
					{
						printf('<span title="%d bytes">%s</span>', $item->getSize(), utils_getFileSizeSuffix($item->getSize()));
					}?></td>
				<td><?php echo $item->getModificationDate() ?></td>
				<?php if ($perm->isGranted(Permission::CHANGEPERMS, $item->getFullPath())): ?>
					<td>
						<?php if ($perm->isGranted(Permission::CHANGEPERMS, $item->getFullPath())): ?>
						<a title="<?php $tr->trans('file.changepermissions') ?>" href="?action=changepermissions&p=<?php echo rawurlencode($item->getFullPath()) ?>"><?php echo $item->getPermissions() ?></a>
						<?php else: ?>
							<?php echo $item->getPermissions() ?>
						<?php endif; ?>
					</td>
					<td><?php echo $item->getOwner() . ':' . $item->getGroup() ?></td>
				<?php endif; ?>
				<td class="inline-actions">
					<?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
					<div class="dropdown">
						<button class="btn btn-sm btn-light dropdown-toggle" type="button"
								style="padding-top:0; padding-bottom-0;" id="dropdownFileAction"
								data-bs-toggle="dropdown" aria-expanded="false" title="<?php $tr->trans('file.actions'); ?>">
							...
						</button>
						<ul class="dropdown-menu" aria-labelledby="dropdownFileAction">
							<li><a title="<?php $tr->trans('file.copy')?>"
								href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
								class="dropdown-item"
								onclick="fileActionPreparePre('<?php echo utils_pathToHtml($item->getFullPath()) ?>', 'copy')">
								<i class="fas fa-copy"></i>&nbsp;<?php $tr->trans('file.copy') ?>
							</a></li>
							<li><a title="<?php $tr->trans('file.moverename')?>"
								href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
								class="dropdown-item"
								onclick="fileActionPreparePre('<?php echo utils_pathToHtml($item->getFullPath()) ?>', 'moverename')">
								<i class="fas fas fa-people-carry"></i>&nbsp;<?php $tr->trans('file.moverename') ?>
							</a></li>
							<li><a title="<?php $tr->trans('file.delete')?>"
								href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
								class="dropdown-item"
								onclick="fileActionPreparePre('<?php echo utils_pathToHtml($item->getFullPath()) ?>', 'delete')">
								<i class="fas fa-trash"></i>&nbsp;<?php $tr->trans('file.delete') ?>
							</a></li>
							<?php if (in_array($item->getExtension(), utils_getArchiveExts()) &&
									  (class_exists('PharData') || class_exists('ZipArchive'))): ?>
							<li><a title="<?php $tr->trans('file.extract')?>"
								href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
								class="dropdown-item"
								onclick="fileActionPreparePre('<?php echo utils_pathToHtml($item->getFullPath()) ?>', 'extract')">
								<i class="fas fa-external-link-square-alt"></i>&nbsp;<?php $tr->trans('file.extract') ?>
							</a></li>
							<?php endif; ?>
							<?php if (!$item->getIsDir()): ?>
							<li><a href="?action=download&p=<?php echo rawurlencode($item->getFullPath()) ?>&dlf"
								class="dropdown-item">
								<i class="fas fa-download"></i>&nbsp;<?php $tr->trans('file.download') ?>
							</a></li>
							<?php endif; ?>
						</ul>
					</div>
					<?php else: ?>
						<a href="?action=download&p=<?php echo rawurlencode($item->getFullPath()) ?>&dlf"
								title="<?php $tr->trans('file.download') ?>">
							<i class="fas fa-download btn btn-primary"></i>
						</a>
					<?php endif; // isGranted(Modify, $path) ?>
				</td>
			</tr>
<?php
	flush();
	$index_gen++;
	if ($item->getIsDir()) $num_folders++;
	else $num_files++;
	$all_files_size += $item->getSize();
}
?>
			</tbody>
			<tfoot>
<?php
if (empty($VIEWVARS['items']))
{
?>
			<tr>
				<td><!-- checkbox--></td>
				<td colspan="<?php echo ($perm->isGranted(Permission::CHANGEPERMS, $path)) ? '6' : '4' ?>"><em><?php $tr->trans('file.folderempty') ?></em></td>
			</tr>
<?php
}
if ($perm->isGranted(Permission::SHOWSYSINFO, null))
{
?>
			<tr>
				<td class="gray"></td>
				<td class="gray" colspan="<?php echo ($perm->isGranted(Permission::CHANGEPERMS, $path)) ? '6' : '4' ?>">
				<?php
					$tr->trans('file.size');
					printf (': <span title="%ld bytes" class="meminfo badge bg-light text-dark">%s</span>' . PHP_EOL, $all_files_size, utils_getFileSizeSuffix($all_files_size));
					$tr->trans('file.files');
					printf (': <span class="meminfo badge bg-light text-dark">%ld</span>' . PHP_EOL, $num_files);
					$tr->trans('file.folders');
					printf (': <span class="meminfo badge bg-light text-dark">%ld</span>' . PHP_EOL, $num_folders);

					$mem_usage_size = @memory_get_usage(true);
					$tr->trans('file.memory');
					printf (': <span title="%ld bytes" class="meminfo badge bg-light text-dark">%s</span>' . PHP_EOL, $mem_usage_size, utils_getFileSizeSuffix($mem_usage_size));
					$free_disk_size = @disk_free_space($path);
					$tr->trans('file.freedisk');
					printf (': <span title="%ld bytes" class="meminfo badge bg-light text-dark">%s</span>' . PHP_EOL, $free_disk_size, utils_getFileSizeSuffix($free_disk_size));
					$partition_disk_size = @disk_total_space($path);
					$tr->trans('file.partitionsize');
					printf (': <span title="%ld bytes" class="meminfo badge bg-light text-dark">%s</span>' . PHP_EOL, $partition_disk_size, utils_getFileSizeSuffix($partition_disk_size));
				?>
				</td>
			</tr>
<?php
}
?>
		</tfoot>
	</table>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-9">
		<ul class="list-inline footer-action m-2">
			<!-- common section -->
			<li class="list-inline-item">
				<a href="#" class="btn btn-small btn-outline-primary btn-2" onclick="select_all();return false;">
					<i class="fas fa-check-square"></i>&nbsp;<?php $tr->trans('file.selectall') ?>
				</a>
			</li>
			<li class="list-inline-item">
				<a href="#" class="btn btn-small btn-outline-primary btn-2" onclick="unselect_all();return false;">
					<i class="fas fa-window-close"></i>&nbsp;<?php $tr->trans('file.unselectall') ?>
				</a>
			</li>
			<li class="list-inline-item">
				<a href="#" class="btn btn-small btn-outline-primary btn-2" onclick="invert_all();return false;">
					<i class="fas fa-list-alt"></i>&nbsp;<?php $tr->trans('file.invertselection') ?>
				</a>
			</li>
			<?php if (class_exists('ZipArchive')): ?>
			<li class="list-inline-item" id="multipleDownload" style="display:none">
				<a href="#"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('downloadzip');">
					<i class="fas fa-download"></i>&nbsp;<?php $tr->trans('file.downloadzip') ?>
				</a>
			</li>
			<?php endif; ?>
		</ul>
		<?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
		<ul class="list-inline footer-action m-2">
			<li class="list-inline-item" id="multipleCopy" style="display:none">
				<a href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('copy');">
					<i class="fas fa-copy"></i>&nbsp;<?php $tr->trans('file.copy') ?>
				</a>
			</li>
			<li class="list-inline-item" id="multipleMoverename" style="display:none">
				<a href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('moverename');">
					<i class="fas fas fa-people-carry"></i>&nbsp;<?php $tr->trans('file.moverename') ?>
				</a>
			</li>
			<li class="list-inline-item" id="multipleDelete" style="display:none">
				<a href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('delete');">
					<i class="fas fa-trash"></i>&nbsp;<?php $tr->trans('file.delete') ?>
				</a>
			</li>
			<li class="list-inline-item" id="multipleCompress" style="display:none">
				<a href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('compress');">
					<i class="fas fa-file-archive"></i>&nbsp;<?php $tr->trans('file.compress') ?>
				</a>
			</li>
			<li class="list-inline-item" id="multipleExtract" style="display:none">
				<a href="#" data-bs-toggle="modal" data-bs-target="#fileActionModal"
				   class="btn btn-small btn-outline-primary btn-2"
				   onclick="fileActionPreparePreMultiple('extract');">
					<i class="fas fa-external-link-square-alt"></i>&nbsp;<?php $tr->trans('file.extract') ?>
				</a>
			</li>
		</ul>
		<?php endif; ?>
	</div>
</div>
<?php include ('views/footers.php'); ?>
