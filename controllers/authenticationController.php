<?php
require_once ('controllers/baseController.php');
require_once ('utils/authenticator.php');
require_once('config.php');

class AuthenticationController extends BaseController
{
	public function loginAction()
	{
		global $CONFIG;
		if (!$CONFIG['useauth'])
		{
			$this->redirect('?action=ls&p=/');
		}
		$isLogged = false;
		if (isset ($_POST['username'], $_POST['password']))
		{
			$isLogged = Authenticator::login($_POST['username'], $_POST['password'], isset($_POST['rememberme']) ? $_POST['rememberme'] == '1' : false);
		}

		$wantedurl = null;
		if (isset($_REQUEST['wantedurl']))
		{
			$wantedurl = $_REQUEST['wantedurl'];
			if ($wantedurl == '' || $wantedurl == 'null') $wantedurl = null;
		}

		if ($isLogged)
		{
			if ($wantedurl != null)
			{
				$this->redirect($wantedurl);
			}
			else
			{
				$this->redirect('?action=ls&p=' . $this->getCurrentUser()->getRootDirectory());
			}
		}
		else
		{
			if (isset($_POST['username']))
			{
				$this->view('auth/login.php', array('msg' => $this->getTranslator()->translate('login.failed'), 'wantedurl' => $wantedurl));
			}
			else
			{
				$this->view('auth/login.php', array('wantedurl' => $wantedurl));
			}
		}
	}

	public function logoutAction()
	{
		global $CONFIG;
		Authenticator::logout();
		if ($CONFIG['useauth'])
		{
			$this->redirect('?action=login');
		}
		else
		{
			$this->redirect('?action=ls&p=/');
		}
	}
}
?>
