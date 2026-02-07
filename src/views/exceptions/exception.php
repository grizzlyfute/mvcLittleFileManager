<?php
$title = '';
$path = '/';
include ('views/headers.php'); ?>
  <h1><center><?php $tr->trans('err.code')?>&nbsp;<?php echo http_response_code()?></center></h1>
  <pre>
    <?php echo $VIEWVARS['errormsg']?>
  </pre>
  <br/>
  <?php if ($CONFIG['debug'])
  {
    echo '<hr/>' . PHP_EOL;
    echo '<pre>' . PHP_EOL;
    debug_print_backtrace();
    echo 'Last Error: ' . PHP_EOL;
    var_dump(error_get_last());
    echo '</pre>' . PHP_EOL;
  }
  syslog (LOG_ERR, $VIEWVARS['errormsg']);
  ?>
  <button type="button" class="btn btn-secondary" onclick="history.back()">
     <i class="fas fa-chevron-circle-left go-back"></i>&nbsp;<?php $tr->trans('common.back') ?>
  </button>
<?php include ('views/footers.php'); ?>
