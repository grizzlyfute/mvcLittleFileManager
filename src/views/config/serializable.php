<?php
$title = $tr->translate($VIEWVARS['title']);
$path = null;
include ('views/headers.php'); ?>
<script>
function onCheckboxChanged(element)
{
  other = document.getElementById ('default_' + element.name);
  if (element.checked) other.disabled = true;
  else other.disabled = false;
}

// Selecting multiple from an html select element without using ctrl key
window.onmousedown = function (e) {
    var el = e.target;
    if (el.tagName.toLowerCase() == 'option' && el.parentNode.hasAttribute('multiple')) {
        e.preventDefault();

        // toggle selection
        if (el.hasAttribute('selected')) el.removeAttribute('selected');
        else el.setAttribute('selected', '');

        // hack to correct buggy behavior
        var select = el.parentNode.cloneNode(true);
        el.parentNode.parentNode.replaceChild(select, el.parentNode);
    }
}
</script>
<div class="container col-md-8 offset-md-2 pt-3">
  <div class="card mb-2">
    <!-- <div class="card-header">
      <h1 class="card-header"><?php $tr->trans($VIEWVARS['title']) ?></h1>
    </div> -->
    <div class="card-body">
      <form method="POST" action="?action=setdata" class="form-inline">
        <input type="hidden" name="entityname" value="<?php echo $VIEWVARS['entityname']?>"/>
<?php
foreach ($VIEWVARS['arrayobject'] as $name => $value)
{
  $type = gettype($value);
  if (in_array ($name, $VIEWVARS['rofields'])) $ro = 'readonly';
  else $ro = '';
?>
  <?php if (strstr ($name, 'password')): ?>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="password" <?php echo $ro?>/>
    </div>
  </div>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.confirmpassword') ?></label>
    </div>
    <div class="col-8">
      <input class="form-control rounded-3" name="<?php echo $name ?>confirm" type="password" <?php echo $ro?>/>
    </div>
  <div>
  <?php elseif ($type == 'boolean'): ?>
  <div class="row">
    <div class="col-4">
      <label class="form-text form-label" ><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <div class="form-check form-switch">
        <input id="default_<?php echo $name ?>" type="hidden" name="<?php echo $name ?>" value="off"/><?php /* if checkbox is unchecked. Both send to the server */?>
        <input class="form-check-input rounded-pill mt-2" name="<?php echo $name ?>" type="checkbox" <?php echo($value ? 'checked' : '')?>
            onchange="onCheckboxChanged(this)" <?php echo $ro?>/>
        <label class="form-check-label" for="<?php echo $name ?>"></label>
      </div>
    </div>
  </div>
  <?php elseif ($type == 'integer'): ?>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="number" step="1" value="<?php echo $value ?>" <?php echo $ro?>/>
    </div>
  </div>
  <?php elseif ($type == 'double'): ?>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="number" step="0.001" value="<?php echo $value ?>" <?php echo $ro?>/>;
    </div>
  </div>
  <?php elseif ($type == 'string' || $type == 'NULL'): ?>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label content_center" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <?php if (array_key_exists($name, $VIEWVARS['possiblesvalues'])): ?>
      <select class="form-select rounded-3" name="<?php echo $name ?>" <?php echo $ro?>>
      <?php
      $options = $VIEWVARS['possiblesvalues'][$name];
      $isSequential = !empty($options) && isset($options[0]);
      foreach ($options as $opt_key => $opt_val)
      {
        if ($isSequential)
        {
          echo '<option value="' . $opt_val . '"' . ($opt_val == $value ? ' selected' : '') . '>' . $opt_val . '</option>' . PHP_EOL;
        }
        else
        {
          echo '<option value="' . $opt_key . '"' . ($opt_key == $value ? ' selected' : '') . '>' . $opt_val . '</option>' . PHP_EOL;
        }
      }
      ?>
      </select>
      <?php else: ?>
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="text" value="<?php echo $value ?>" <?php echo $ro?>/>
      <?php endif; ?>
    </div>
  </div>
  <?php elseif ($type == 'array'): ?>
  <div class="form-text row">
    <div class="col-4">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-8">
      <?php if (array_key_exists($name, $VIEWVARS['possiblesvalues'])): ?>
      <input type="hidden" name="<?php echo $name ?>[]" value=""/>
      <select class="form-select rounded-3" name="<?php echo $name ?>[]" multiple <?php echo $ro?>>
      <?php foreach ($VIEWVARS['possiblesvalues'][$name] as $option)
      {
        echo '<option value="' . $option . '"' . (in_array($option, $value) ? ' selected' : '') . '>' . $option . '</option>' . PHP_EOL;
      }
      ?>
      </select>
      <?php else: ?>
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="text" value="<?php echo implode(',', $value)?>" title="use ',' as separator"/>
      <?php endif; ?>
    </div>
  </div>
  <?php elseif ($type == 'object'): ?>
  <div class="form-text row">
    <div class="col-sm-3">
      <label class="form-label" for="<?php echo $name ?>"><?php $tr->trans('setting.' . $name) ?></label>
    </div>
    <div class="col-sm-5">
      <input class="form-control rounded-3" name="<?php echo $name ?>" type="text" value="<?php echo json_encode($value) ?>" <?php echo $ro?>/>
    </div>
  </div>
  <?php else: ?>
  <div class="row">
    <div class="col">
      <span>Type not managed <?php echo $type . '<i>(' . $name . ')</i>' ?> </span>;
    </div>
  </div>
  <?php endif;
  }
  ?>
        <div class="mt-2 form-group row">
          <div class="col">
            <div class="float-end">
            <button type="button" class="btn btn-secondary rounded-2" onclick="history.back()">
              <i class="fas fa-times-circle"></i>&nbsp;<?php $tr->trans('common.back') ?>
            </button>
            <button type="submit" class="btn btn-success rounded-2">
              <i class="fas fa-check-circle"></i>&nbsp;<?php $tr->trans('common.ok')?>
            </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include ('views/footers.php'); ?>
