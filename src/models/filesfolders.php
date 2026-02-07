<?php

class FilesFolders
{
  private $name;
  public function getName()
  {
    return $this->name;
  }
  public function setName($val)
  {
    $this->name = $val;
    return $this;
  }

  private $isDir;
  public function getIsDir()
  {
    return $this->isDir;
  }
  public function setIsDir($val)
  {
    $this->isDir = $val;
    return $this;
  }

  private $isLink;
  public function getIsLink()
  {
    return $this->isLink;
  }
  public function setIsLink($val)
  {
    $this->isLink = $val;
    return $this;
  }

  private $fullpath;
  public function getFullpath()
  {
    return $this->fullpath;
  }
  public function setFullpath($val)
  {
    $this->fullpath = $val;
    return $this;
  }

  private $owner;
  public function getOwner()
  {
    return $this->owner;
  }
  public function setOwner($val)
  {
    $this->owner = $val;
    return $this;
  }

  private $group;
  public function getGroup()
  {
    return $this->group;
  }
  public function setGroup($val)
  {
    $this->group = $val;
    return $this;
  }

  private $permissions;
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setPermissions($val)
  {
    $this->permissions = $val;
    return $this;
  }

  private $creationDate;
  public function getCreationDate()
  {
    return $this->creationDate;
  }
  public function setCreationDate($val)
  {
    $this->creationDate = $val;
    return $this;
  }

  private $modificationDate;
  public function getModificationDate()
  {
    return $this->modificationDate;
  }
  public function setModificationDate($val)
  {
    $this->modificationDate = $val;
    return $this;
  }

  private $size;
  public function getSize()
  {
    return $this->size;
  }
  public function setSize($val)
  {
    $this->size = $val;
    return $this;
  }

  private $extension;
  public function getExtension()
  {
    return $this->extension;
  }
  public function setExtension($val)
  {
    $this->extension = $val;
    return $this;
  }
}
