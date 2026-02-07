<?php

require_once("models/user.php");
require_once("utils/utils.php");
require_once("config.php");

class Permission
{
  public const ADMIN_USERNAME = 'admin';

  public const VIEW = 'view';
  public const ADMIN = 'admin';
  public const MODIFY = 'modify';
  public const CHANGEPERMS = 'changeperms';
  public const SHOWSYSINFO = 'showsysinfo';
  public const USERS = 'users';
  public const PREFERENCES = 'preference';

  private $user = null;
  public function __construct(?User $user)
  {
    $this->user = $user;
  }

  public static function getPossiblePermissions(): array
  {
    return array
    (
      self::ADMIN,
      self::MODIFY,
      self::CHANGEPERMS,
      self::SHOWSYSINFO,
      self::USERS,
      self::PREFERENCES,
    );
  }

  public function isGranted($perm, $arg): bool
  {
    global $CONFIG;
    $ret = false;
    $curuser = Authenticator::getCurrentUser();
    if ($CONFIG['useauth'] && !$curuser) return false;
    if ($curuser && $curuser->getUserName() == self::ADMIN_USERNAME) return true;
    if ($curuser && in_array(self::ADMIN, $curuser->getPermissions())) return true;

    $root = '/';
    if ($curuser != null) $root .= $curuser->getRootDirectory();
    $root = utils_cleanPath($root);

    switch ($perm)
    {
      case self::ADMIN:
        $ret = $curuser && ($curuser->getUserName() == self::ADMIN_USERNAME ||
          in_array(self::ADMIN, $curuser->getPermissions()));
        break;

      // Files sections
      case self::VIEW:
        if (gettype ($arg) != 'string')
        {
          $ret = false;
        }
        else if (!utils_isPathIncludeInto($root, $arg, false))
        {
          $ret = false;
        }
        else
        {
          $ret = true;
        }
        break;

      case self::MODIFY:
        if (!$this->isGranted(self::VIEW, $arg))
        {
          $ret = false;
        }
        else if ($curuser == null)
        {
          $ret = true;
        }
        else if (in_array(self::MODIFY, $curuser->getPermissions()))
        {
          $ret = true;
          foreach ($curuser->getExceptionsDirectories() as $dir)
          {
            if ($dir && utils_isPathIncludeInto($dir, $arg, false))
            {
              $ret = false;
              break;
            }
          }
        }
        else if (!in_array(self::MODIFY, $curuser->getPermissions()))
        {
          $ret = false;
          foreach ($curuser->getExceptionsDirectories() as $dir)
          {
            if ($dir && utils_isPathIncludeInto($dir, $arg, false))
            {
              $ret = true;
              break;
            }
          }
        }
        else
        {
          $ret = false;
        }
        break;

      case self::CHANGEPERMS:
        $ret = $this->isGranted(self::MODIFY, $arg);
        $ret &= $curuser == null || in_array(self::CHANGEPERMS, $curuser->getPermissions());
        break;

      case self::SHOWSYSINFO:
        $ret = gettype($arg) == 'NULL';
        $ret &= $curuser == null || in_array(self::SHOWSYSINFO, $curuser->getPermissions());
        break;

      case self::USERS:
        // List all, create new
        if ($arg == null)
        {
          $ret = $curuser != null && in_array(self::USERS, $curuser->getPermissions());
        }
        // Edit, delete
        else if ($arg instanceof User)
        {
          $ret = $curuser != null &&
          (
            in_array(self::USERS, $curuser->getPermissions())
            // $$$$ revoked as security issues: user may change in admin / grant permission || $curuser->getUserName() == $arg->getUserName()
          );
        }
        else
        {
          $ret = false;
        }
        break;

      case self::PREFERENCES:
        $ret = $curuser == null || in_array(self::PREFERENCES, $curuser->getPermissions());
        break;

      default:
        $ret = false;
    }

    return $ret;
  }
}
?>
