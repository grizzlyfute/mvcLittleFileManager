<?php
require_once('controllers/baseController.php');
require_once('models/user.php');
require_once('models/serializableEntity.php');
require_once('models/permission.php');
require_once('exceptions/forbiddenException.php');
require_once('utils/translator.php');
require_once('config.php');

class ConfigurationController extends BaseController
{
	private function possibleArrayValues() : array
	{
		return array
		(
			'permissions' => Permission::getPossiblePermissions(),
			'lang' => array ('en', 'fr'),
			'timezone' => array ('Etc/GMT', 'Etc/GMT+0', 'Etc/GMT+1', 'Etc/GMT+10', 'Etc/GMT+11', 'Etc/GMT+12', 'Etc/GMT+2', 'Etc/GMT+3', 'Etc/GMT+4', 'Etc/GMT+5', 'Etc/GMT+6', 'Etc/GMT+7', 'Etc/GMT+8', 'Etc/GMT+9', 'Etc/GMT-0', 'Etc/GMT-1', 'Etc/GMT-10', 'Etc/GMT-11', 'Etc/GMT-12', 'Etc/GMT-13', 'Etc/GMT-14', 'Etc/GMT-2', 'Etc/GMT-3', 'Etc/GMT-4', 'Etc/GMT-5', 'Etc/GMT-6', 'Etc/GMT-7', 'Etc/GMT-8', 'Etc/GMT-9', 'Etc/GMT0', 'Etc/Universal', 'Etc/UTC', 'Etc/Zulu'),
			'dateformat' => array('Y-m-d H:i:s', 'd/m/Y H:i:s', 'F j, Y, g:i a', 'm.d.y', 'Ymd', 'D M j G:i:s T Y'),
			'onlineviewer' => array ('none', 'google', 'microsoft'),
		);
	}

	// Check config, and create missing parameters
	private function fixAppConfig(SerializableEntity &$config): bool
	{
		$ret = true;

		// Solve relative paths
		if ($ret)
		{
			$val = $config->getValue('rootdirectory');
			if ($val) $config->setValue('rootdirectory', utils_cleanPath($val));
		}
		if ($ret)
		{
			$val = $config->getValue('tmppath');
			if ($val) $config->setValue('tmppath', utils_cleanPath($val));
		}

		// If user enabled and no user present, then create at least an admin
		if ($ret && $config->getValue('useauth'))
		{
			Authenticator::loadUsers();
			if (count (Authenticator::getAllUsers()) <= 0)
			{
				$user = new User();
				$user->setUserName(Permission::ADMIN_USERNAME);
				$user->setPasswordHash(Authenticator::getPasswordHashFormString('admin'));
				$user->setIsActive('true');
				Authenticator::setEditUser($user);
				$ret = Authenticator::saveUsers();
			}
		}

		return $ret;
	}

	public function newUserAction()
	{
		$user = new User();
		if (!$this->getPermissions()->isGranted(Permission::USERS, $user))
		{
			throw new ForbiddenException('Access denied');
		}
		$this->view('config/serializable.php', array
		(
			'title' => 'setting.newusertitle',
			'entityname' => 'newuser',
			'arrayobject' => $user->toArray(),
			'possiblesvalues' => $this->possibleArrayValues(),
			'rofields' => array(),
		));
	}

	public function userSettingsAction()
	{
		$user = null;
		if (isset($_REQUEST['username']))
		{
			$username = $_REQUEST['username'];
			$user = Authenticator::getUser($username);
		}
		if (!$user || !$this->getPermissions()->isGranted(Permission::USERS, $user))
		{
			throw new ForbiddenException('Access denied');
		}
		$this->view('config/serializable.php', array
		(
			'title' => 'setting.usersettingtitle',
			'entityname' => 'usersetting',
			'arrayobject' => $user->toArray(),
			'possiblesvalues' => $this->possibleArrayValues(),
			'rofields' => array('username'),
		));
	}

	public function appSettingsAction()
	{
		if (!$this->getPermissions()->isGranted(Permission::PREFERENCES, null))
		{
			throw new ForbiddenException('Access denied');
		}
		global $CONFIG;
		$this->view('config/serializable.php', array
		(
			'title' => 'setting.appsettingtitle',
			'entityname' => 'appsetting',
			'arrayobject' => $CONFIG,
			'possiblesvalues' => $this->possibleArrayValues(),
			'rofields' => array('rootdirectory'),
		));
	}

	public function usersListAction()
	{
		if (!$this->getPermissions()->isGranted(Permission::USERS, null))
		{
			throw new ForbiddenException('Access denied');
		}
		$this->view('config/userslist.php', array
		(
			'users' => Authenticator::getAllUsers(),
		));
	}

	public function deleteUserAction()
	{
		$username = null;
		if (isset($_REQUEST['username'])) $username = $_REQUEST['username'];
		if (!$username ||
			($this->getCurrentUser() != null && $this->getCurrentUser()->getUserName() == $username) ||
			$username == 'admin')
		{
			$this->setMessage ('invalid user "' . $username . '"', 'error');
			$this->usersListAction();
			return;
		}
		$user = Authenticator::getUser($username);
		if (!$user || !$this->getPermissions()->isGranted(Permission::USERS, $user))
		{
			throw new ForbiddenException('Access denied');
		}
		Authenticator::deleteUser($user);
		if (Authenticator::saveUsers())
		{
			$this->setMessage($this->getTranslator()->translate('common.success'), 'success');
		}
		else
		{
			$this->setMessage('Fail to delete user ' . $username, 'error');
		}
		$this->redirect('?action=userslist');
	}

	private function setDataForUser (User $user, array $data): bool
	{
		$passwdHash = $user->getPasswordHash();
		$oldPerms = $user->getPermissions();
		$user->fromStringArray($data);
		$user->setPasswordHash($passwdHash);
		$user->setRootDirectory(utils_cleanPath($user->getRootDirectory()));
		// Can not grant not owned permissions
		if (!$this->getPermissions()->isGranted(PERMISSION::ADMIN, $this->getCurrentUser()))
		{
			if ($this->getCurrentUser()->getPermissions())
			{
				$curPerms = $this->getCurrentUser()->getPermissions();
			}
			else
			{
				$curPerms = array();
			}
			$newPerms = array_filter($user->getPermissions(), function ($perm) use ($oldPerms)
			{
				return in_array($perm, $oldPerms);
			});
			$user->setPermissions($newPerms);
		}

		if (isset ($data['password'], $data['passwordconfirm']))
		{
			$pswd1 = $_POST['password'];
			$pswd2 = $_POST['passwordconfirm'];
			if ($pswd1 != $pswd2)
			{
				$this->setMessage('password not matching', 'warning');
				return false;
			}
			else if ($pswd1 != '')
			{
				$user->setPasswordHash(Authenticator::getPasswordHashFormString($pswd1));
			}
		}
		Authenticator::setEditUser($user);
		return Authenticator::saveUsers();
	}

	public function setDataAction()
	{
		global $CONFIG;
		$err = false;
		$name = isset($_POST['entityname']) ? $_POST['entityname'] : '';
		// Construct input array
		switch ($name)
		{
			case 'newuser':
				if (!$this->getPermissions()->isGranted(Permission::USERS, null))
				{
					throw new ForbiddenException('Access denied');
				}
				$user = null;
				if (!isset($_REQUEST['username']) || !$_REQUEST['username'])
				{
					$this->setMessage('No user name', 'error');
					$this->usersListAction();
					return;
				}
				$username = $_REQUEST['username'];
				if (!$username || !preg_match('/^[a-zA-Z0-9_]+$/', $username))
				{
					$this->setMessage('"' . $username . '" invalid', 'error');
					$this->usersListAction();
					return;
				}
				$user = new User();
				if (Authenticator::getUser($username) != null)
				{
					$this->setMessage('"' . $username . '" already exists', 'error');
					$this->newUserAction();
					return;
				}
				if (!$this->setDataForUser($user, $_POST))
				{
					$error = error_get_last();
					$errmsg = ($error != NULL) ? $error['message'] : '';
					$this->setMessage('Error while saving user ' . $errmsg, 'error');
					$this->newUserAction();

				}
				break;

			case 'usersetting':
				if (!isset($_REQUEST['username']))
				{
					$this->setMessage('No user name');
					$this->redirect('?action=ls&p=/');
					return;
				}
				$username = $_REQUEST['username'];
				if (!$username || !preg_match('/^[a-zA-Z0-9_]+$/', $username))
				{
					$this->setMessage('"' . $username . '" invalid', 'error');
					$this->redirect('?action=ls&p=/');
					return;
				}

				$user = Authenticator::getUser($username);
				if ($user == null)
				{
					$this->setMessage('"' . $username . '" does not exist', 'error');
					$this->redirect('?action=ls&p=/');
					return;
				}
				if (!$this->getPermissions()->isGranted(Permission::USERS, $user))
				{
					throw new ForbiddenException('Access denied');
				}

				if ($user->getUserName() != $username)
				{
					if (!$user->getUserName() || !preg_match('/^[a-zA-Z0-9_]+$/', $user->getUserName() ))
					{
						$this->setMessage('"' . $user->getUserName() . '" invalid', 'error');
						$this->redirect('?action=ls&p=/');
						return;
					}
					if (Authenticator::getUser($user->getUserName()) != null)
					{
						$this->setMessage('"' . $username . '" already exists', 'error');
						$this->userSettingsAction();
						return;
					}
				}

				if (!$this->setDataForUser($user, $_POST))
				{
					$error = error_get_last();
					$errmsg = ($error != NULL) ? $error['message'] : '';
					$this->setMessage('Error while saving user ' . $errmsg, 'error');
					$this->userSettingsAction();
					return;
				}
				break;

			case 'appsetting':
				if (!$this->getPermissions()->isGranted(Permission::PREFERENCES, null))
				{
					throw new ForbiddenException('Access denied');
				}
				$config = new SerializableEntity($CONFIG);
				$config->fromStringArray($_POST);
				if (!$this->fixAppConfig($config))
				{
					$error = error_get_last();
					$errmsg = ($error != NULL) ? $error['message'] : '';
					$this->setMessage('Error while checking configuration' . $errmsg, 'error');
					$this->appSettingsAction();
				}
				if (!save_config($config->toArray()))
				{
					$error = error_get_last();
					$errmsg = ($error != NULL) ? $error['message'] : '';
					$this->setMessage('Error while saving settings ' . $errmsg, 'error');
					$this->appSettingsAction();
					return;
				}
				break;

			default:
				throw new BadRequestException('Unknown name "' . $name . '"');
				break;
		}

		$this->setMessage($this->getTranslator()->translate('common.success'), 'success');
		$this->redirect('?action=ls&p=/');
	}
}
?>
