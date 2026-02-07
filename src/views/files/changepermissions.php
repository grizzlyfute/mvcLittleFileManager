<?php
$title = $tr->translate('file.changepermissions');
$path = $VIEWVARS['fullpath'];
include ('views/headers.php'); ?>
<div class="path">
  <div class="card mb-2">
    <h6 class="card-header">
    <strong><?php $tr->trans('file.name') ?>:</strong><i><?php echo $path?></i>
    </h6>
    <div class="card-body">
      <p class="card-text">
      </p>
      <form action="?action=dochangepermissions" method="POST">
        <input type="hidden" name="p" value="<?php echo $path ?>">

        <table class="table compact-table">
          <tr>
            <td></td>
            <td><b><?php $tr->trans('file.owner') ?></b></td>
            <td><b><?php $tr->trans('file.group') ?></b></td>
            <td><b><?php $tr->trans('file.other') ?></b></td>
          </tr>
          <tr>
            <td style="text-align: right"><b><?php $tr->trans('file.permread') ?></b></td>
            <td><label><input type="checkbox" name="ur" value="1"<?php echo ($VIEWVARS['mode'] & 00400) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="gr" value="1"<?php echo ($VIEWVARS['mode'] & 00040) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="or" value="1"<?php echo ($VIEWVARS['mode'] & 00004) ? ' checked' : '' ?>></label></td>
          </tr>
          <tr>
            <td style="text-align: right"><b><?php $tr->trans('file.permwrite') ?></b></td>
            <td><label><input type="checkbox" name="uw" value="1"<?php echo ($VIEWVARS['mode'] & 00200) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="gw" value="1"<?php echo ($VIEWVARS['mode'] & 00020) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="ow" value="1"<?php echo ($VIEWVARS['mode'] & 00002) ? ' checked' : '' ?>></label></td>
          </tr>
          <tr>
            <td style="text-align: right"><b><?php $tr->trans('file.permexecute') ?></b></td>
            <td><label><input type="checkbox" name="ux" value="1"<?php echo ($VIEWVARS['mode'] & 00100) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="gx" value="1"<?php echo ($VIEWVARS['mode'] & 00010) ? ' checked' : '' ?>></label></td>
            <td><label><input type="checkbox" name="ox" value="1"<?php echo ($VIEWVARS['mode'] & 00001) ? ' checked' : '' ?>></label></td>
          </tr>
        </table>

        <div class="float-end">
        <p>
          <b><a href="?p=<?php echo $VIEWVARS['parent']?>" class="btn btn-outline-primary"><i class="fas fa-times-circle">&nbsp;</i><?php $tr->trans('common.cancel') ?></a></b>
          <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i>&nbsp;<?php $tr->trans('common.ok') ?></button> &nbsp;
        </p>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include ('views/footers.php'); ?>
