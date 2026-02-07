<?php
require_once ('exceptions/baseException.php');

class NotFoundException extends BaseException
{
  public function __construct (string $message = "" , int $code = 404, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
?>
