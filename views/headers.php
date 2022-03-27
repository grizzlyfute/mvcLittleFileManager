<?php
	header('Content-Type: text/html; charset=utf-8');
	// No cache
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
	// Frame management
	header('X-Frame-Options: sameorigin');
	header('X-XSS-Protection: 1');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<meta name="description" content=""/>
	<!-- <meta name="author" content=""/> -->
	<meta name="robots" content="noindex, nofollow"/>
	<meta name="googlebot" content="noindex"/>
	<link rel="icon" href="favicon.png" type="image/png"/>
	<title><?php echo $CONFIG['apptitle'] ?></title>
	<link rel="stylesheet" href="libs/bootstrap-5.1.0/css/bootstrap.min.css"/>
	<style>
		/* fixing bootstrap issue */
		.custom-control-label { vertical-align: top; }
		.custom-control-input { position: relative; margin: 15%; }
	</style>
	<link rel="stylesheet" href="libs/fontawesome-free-5.15.4-web/css/all.min.css"/>
	<link rel="stylesheet" href="style.css"/>
	<script src="libs/jquery-3.6.0/jquery-3.6.0.min.js" type="text/javascript"></script>
	<script src="libs/bootstrap-5.1.0/js/bootstrap.bundle.min.js" type="text/javascript"></script>
	<script src="libs/datatables-1.11.0/datatables.min.js" type="text/javascript"></script>
</head>
	<body>
		<?php
		if (isset ($VIEWVARS['msg']) && $VIEWVARS['msg'])
		{
			if (isset ($VIEWVARS['msgclass'])) $msgclass =  $VIEWVARS['msgclass'];
			else $msgclass = 'info';
			?>
			<script type="text/javascript">
			$(document).ready(function()
			{
		<?php if ($msgclass == 'success'): ?>
				setTimeout(function(){ $("#messagediv").slideUp(400) }, 2500);
		<?php else: ?>
				setTimeout(function(){ $("#messagediv").slideUp(400) }, 14000);
		<?php endif; ?>
			});
			</script>

		<div id="messagediv" class="toast-container position-absolute p-3 top-0 start-50 translate-middle-x w-75" style="z-index: 10;"> <!-- top-center - 75%-->
			<div class="toast fade show align-items-center bg-<?php echo $msgclass ?> border-0 w-100 rounded" role="alert" aria-live="assertive" aria-atomic="true">
				<!-- <div class="toast-header"></div> -->
				<div class="toast-body">
				  <?php echo $VIEWVARS['msg'] ?>
				  <button type="button" class="btn-close me-2 m-auto float-end" data-bs-dismiss="toast" aria-label="Close" onClick="$('#messagediv').slideUp(400);" title="<?php $tr->trans('common.close')?>"></button>
				</div>
			</div>
		</div>

		<?php
		}
		include ('views/navbar.php');
		?>
