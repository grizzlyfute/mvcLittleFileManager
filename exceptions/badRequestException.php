<?php
require_once ('exceptions/baseException.php');

class BadRequestException extends BaseException
{
	public function __construct (string $message = "" , int $code = 400, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>
