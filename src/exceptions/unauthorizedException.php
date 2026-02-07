<?php
require_once ('exceptions/baseException.php');

class UnauthorizedException extends BaseException
{
  public function __construct (string $message = "" , int $code = 401, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
?>
