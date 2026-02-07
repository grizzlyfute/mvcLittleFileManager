<?php

require_once('models/serializableEntity.php');
require_once('config.php');

class User extends SerializableEntity
{
  public function __construct()
  {
    parent::__construct();

    // Set default values
    global $CONFIG;
    $this->setUserName('');
    $this->setPasswordHash('');
    $this->setPermissions(array());
    $this->setRootDirectory('/');
    $this->setExceptionsDirectories(array());
    $this->setLanguage($CONFIG['lang']);
    $this->setTimeZone($CONFIG['timezone']);
    $this->setIsActive('false');
    $this->setDateFormat($CONFIG['dateformat']);
  }

  public function getLogin() : string
  {
    $username = ($this->getUserName());
    if ($username === null) return null;
    else return strtolower($username);
  }

  public function getUserName() : string
  {
    return $this->getValue('username');
  }
  public function setUserName(string $val) : User
  {
    $this->setValue('username', $val);
    return $this;
  }
  public function getPasswordHash() : string
  {
    return $this->getValue('password');
  }
  public function setPasswordHash(string $val) : User
  {
    $this->setValue('password', $val);
    return $this;
  }
  public function getPermissions() : array
  {
    return $this->getValue('permissions');
  }
  public function setPermissions(array $val) : User
  {
    $this->setValue('permissions', $val);
    return $this;
  }
  public function getRootDirectory() : string
  {
    return $this->getValue('rootdirectory');
  }
  public function setRootDirectory(string $val) : User
  {
    $this->setValue('rootdirectory', $val);
    return $this;
  }
  public function getExceptionsDirectories() : array
  {
    return $this->getValue('exceptiondir');
  }
  public function setExceptionsDirectories(array $val) : User
  {
    $this->setValue('exceptiondir', $val);
    return $this;
  }
  public function getLanguage() : string
  {
    return $this->getValue('lang');
  }
  public function setLanguage(string $val) : User
  {
    $this->setValue('lang', $val);
    return $this;
  }
  public function getTimeZone() : string
  {
    return $this->getValue('timezone');
  }
  public function setTimeZone(string $val) : User
  {
    $this->setValue('timezone', $val);
    return $this;
  }
  public function getDateFormat() : string
  {
    return $this->getValue('dateformat');
  }
  public function setDateFormat(string $val) : User
  {
    $this->setValue('dateformat', $val);
    return $this;
  }
  public function getIsActive() : bool
  {
    return $this->getValue('isactive');
  }
  public function setIsActive(bool $val) : User
  {
    $this->setValue('isactive', $val);
    return $this;
  }
}
