<?php
$title = $tr->translate('setting.userslisttitle');
$path = null;
include ('views/headers.php'); ?>
<div class="card mb-2 container" style="margin:0em; padding:1em;">
	<!-- <div class="card-header">
		<h1 class="card-header"><?php $tr->trans('setting.userslisttitle') ?></h1>
	</div> -->
	<div class="card-body">
	<div class="">
		<a class="btn btn-small btn-outline-primary btn-2" href="?action=newuser"><i class="fas fa-plus-square"></i>&nbsp;<?php echo $tr->trans('setting.adduser')?></a>
	</div>
	<div class="row">
		<table class="table">
			<thead>
			<tr>
				<th><?php $tr->trans('setting.username')?></th>
				<th><?php $tr->trans('setting.permissions')?></th>
				<th><?php $tr->trans('setting.isactive')?></th>
				<th><?php $tr->trans('setting.actions')?></th>
			</tr>
			</thead>
			<tbody>
				<?php foreach ($VIEWVARS['users'] as $user)
				{
					echo '<tr>' . PHP_EOL;
					echo '<td>' . '<a href="?action=edituser&username=' . $user->getUserName() . '" title="' . $tr->translate('setting.edit') . '">' . $user->getUserName() . '</a></td>' . PHP_EOL;
					echo '<td>' . implode(',', $user->getPermissions()) . '</td>' . PHP_EOL;
					echo '<td>' . ($user->getIsActive() ? $tr->translate('setting.active') : $tr->translate('setting.inactive')) . '</td>' . PHP_EOL;
					echo '<td>' . '<a href="?action=deleteuser&amp;username=' . $user->getUserName() . '" title="' . $tr->translate('setting.delete') .
						'" onClick="return confirm(\'' . $tr->translate('setting.delete') . ' ' . $user->getUserName() . ' ?\');"><i class="fas fa-trash"></i></a></td>' . PHP_EOL;
					echo '<tr>' . PHP_EOL;
				}
				?>
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</div>
	<div class="float-end">
		<button type="button" class="btn btn-outline-secondary rounded" onclick="history.back()">
			<i class="fas fa-chevron-circle-left go-back"></i>&nbsp;<?php $tr->trans('common.back') ?>
		</button>
	</div>
</div>
<?php include ('views/footers.php'); ?>
