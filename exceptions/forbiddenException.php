<?php
require_once ('exceptions/baseException.php');

class ForbiddenException extends BaseException
{
	public function __construct (string $message = "" , int $code = 403, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>
