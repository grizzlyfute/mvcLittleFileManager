<?php

require_once("models/user.php");
require_once("config.php");

class Translator
{
  private $dic;
  public function __construct(?User $curuser)
  {
    global $CONFIG;
    $this->dic = null;
    if (!$this->dic && $curuser != null)
    {
      $this->loadDict($curuser->getLanguage());
    }
    if (!$this->dic)
    {
      $this->loadDict($CONFIG['lang']);
    }
    if (!$this->dic)
    {
      $this->loadDict('en');
    }
    if (!$this->dic)
    {
      $this->dic = array();
    }
  }

  private function loadDict(?string $lang) : void
  {
    if ($lang)
    {
      $filename = 'trans' . DIRECTORY_SEPARATOR . $lang . '.json';
      if (file_exists($filename))
      {
        $this->dic = json_decode(file_get_contents('trans' . DIRECTORY_SEPARATOR . $lang . '.json'), true);
      }
    }
  }

  public function translate(string $key, array $args = array()) : string
  {
    $val = $key;
    if (array_key_exists($key, $this->dic))
    {
      $val = $this->dic[$key];
    }
    $cnt = count ($args);
    for ($i = 0; $i < $cnt && $i < 10; $i++)
    {
      $val = str_replace ('%' . $i, $args[$i], $val);
    }
    return $val;
  }

  public function trans(string $key, array $args = array()) : void
  {
    echo $this->translate($key, $args);
  }
}

?>
