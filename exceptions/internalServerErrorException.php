<?php
require_once ('exceptions/baseException.php');

class InternalServerErrorException extends BaseException
{
	public function __construct (string $message = "" , int $code = 500, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>
