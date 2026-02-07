<?php

require_once('utils/translator.php');
require_once('utils/authenticator.php');
require_once('config.php');
require_once('models/user.php');
require_once('models/permission.php');

class BaseController
{
  private static $translator = null;
  private static $permissions = null;
  private $msg = null;
  private $msgclass = null;
  public function __construct()
  { }

  public function getCurrentUser()
  {
    return Authenticator::getCurrentUser();
  }

  public function getTranslator()
  {
    if (!self::$translator)
    {
      self::$translator = new Translator($this->getCurrentUser());
    }
    return self::$translator;
  }

  public function getPermissions()
  {
    if (!self::$permissions)
    {
      self::$permissions = new Permission($this->getCurrentUser());
    }
    return self::$permissions;
  }

  public function redirect(string $urn): void
  {
    // Store message in session
    if ($this->msg)
    {
      $_SESSION[SESSIONNAME]['msg'] = $this->msg;
      $_SESSION[SESSIONNAME]['msgclass'] = $this->msgclass;
    }
    else
    {
      $_SESSION[SESSIONNAME]['msg'] = null;
      $_SESSION[SESSIONNAME]['msgclass'] = null;
    }

    header('Location: ' . $urn, true, 302);
  }

  public function setMessage(string $msg, string $msgclass) : void
  {
    $msg = htmlspecialchars($msg);
    switch ($msgclass)
    {
      case 'fatal':
      case 'critical':
      case 'error':
      case 'danger':
        $msgclass = 'danger';
        break;

      case 'warning':
        $msgclass = 'warning';
        break;

      case 'info':
        $msgclass = 'info';
        break;

      case 'debug':
      case 'light':
      case 'dark':
      case 'secondary':
        $msgclass = 'secondary';
        break;

      case 'success':
        $msgclass = 'success';
        break;

      default:
        $msgclass = 'primary';
        break;
    }

    if ($this->msg)
    {
      $this->msg .= '<br/>' . PHP_EOL . $msg;
    }
    else
    {
      $this->msg = $msg;
    }
    $this->msgclass = $msgclass;
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public function view(string $viewfile, array $VIEWVARS = array()): void
  {
    $viewfile = 'views/' . $viewfile;
    $viewfile = str_replace('/', DIRECTORY_SEPARATOR, $viewfile);

    // Used in view files
    global $CONFIG;
    $tr = $this->getTranslator();
    $perm = $this->getPermissions();

    if (!$this->msg && isset($_SESSION[SESSIONNAME]['msg']))
    {
      $this->msg = $_SESSION[SESSIONNAME]['msg'];
      $this->msgclass = $_SESSION[SESSIONNAME]['msgclass'];
      $_SESSION[SESSIONNAME]['msg'] = null;
      $_SESSION[SESSIONNAME]['msgclass'] = null;
    }
    if ($this->msg && !isset($VIEWVARS['msg']))
    {
      $VIEWVARS['msg'] = $this->msg;
      $VIEWVARS['msgclass'] = $this->msgclass;
    }

    // Default headers
    header('Content-Type: text/html; charset=utf-8');

    // Generate view
    include ($viewfile);
    // exit(0);

    $this->msg = null;
    $this->msgclass = null;
  }

  // $$$ Debug
  /**
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public function echoJsonAndExit($response): void
  {
    echo json_encode($response);
    exit(0);
  }
}
?>
