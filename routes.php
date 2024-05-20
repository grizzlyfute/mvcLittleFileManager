<?php

require_once('controllers/filesController.php');
require_once('controllers/configurationController.php');
require_once('controllers/authenticationController.php');

// Url => array(controller_name, actionName, basicPremission)
$ROUTES = array
(
	// Default
	'' => array(FilesController::class, 'listAction'),

	 // Files
	'ls' => array(FilesController::class, 'listAction'),
	'lsdirjson' => array(FilesController::class, 'listDirJsonAction'),
	'download' => array(FilesController::class, 'downloadAction'),
	'thumbnail' => array(FilesController::class, 'thumbnailAction'),
	'fileaction' => array(FilesController::class, 'fileAction'),
	'newitem' => array(FilesController::class, 'newItemAction'),
	'view' => array(FilesController::class, 'viewAction'),
	'imagesview' => array(FilesController::class, 'imagesviewAction'),
	'edit' => array(FilesController::class, 'editAction'),
	'upload' => array(FilesController::class, 'uploadAction'),
	'changepermissions' => array(FilesController::class, 'changePermissionsAction'),
	'dochangepermissions' => array(FilesController::class, 'doChangePermissionsAction'),

	// Settings
	'edituser' => array(ConfigurationController::class, 'userSettingsAction'),
	'settings' => array(ConfigurationController::class, 'appSettingsAction'),
	'userslist' => array(ConfigurationController::class, 'usersListAction'),
	'setdata' => array(ConfigurationController::class ,'setDataAction'),
	'newuser' => array(ConfigurationController::class ,'newUserAction'),
	'deleteuser' => array(ConfigurationController::class ,'deleteUserAction'),

	// Auth
	'login' => array(AuthenticationController::class, 'loginAction'),
	'logout' => array(AuthenticationController::class, 'logoutAction'),
);

?>
