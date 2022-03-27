<?php
define ('SESSIONNAME', 'filemanager');

require_once('exceptions/badRequestException.php');
require_once('exceptions/unauthorizedException.php');
require_once('exceptions/forbiddenException.php');
require_once('exceptions/notFoundException.php');
require_once('exceptions/internalServerErrorException.php');
require_once('controllers/baseController.php');
require_once('config.php');
require_once('routes.php');
// General configuration
@set_time_limit(600);

load_config();
date_default_timezone_set($CONFIG['timezone']);
if ($CONFIG['debug'] == true)
{
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
}
else
{
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 0);
}
ini_set('default_charset', 'UTF-8');
ini_set('session.cookie_httponly', '1');
if (version_compare(PHP_VERSION, '5.6.0', '<') && function_exists('mb_internal_encoding'))
{
	mb_internal_encoding('UTF-8');
}
if (function_exists('mb_regex_encoding'))
{
	mb_regex_encoding('UTF-8');
}
// Display only source from this site
// header('Content-Security-Policy', 'default-src \'self\'; img-src *; child-src *');
header('Content-Security-Policy: efault-src \'self\'');

session_cache_limiter('');
session_name(SESSIONNAME);
session_start();

// Do job
$baseController = new BaseController();
try
{
	if (isset($_GET['action']))
	{
		$action = $_GET['action'];
	}
	else
	{
		$action = '';
	}
	$action = filter_var($action, FILTER_SANITIZE_URL);

	if ($CONFIG['useauth'])
	{
		if ($baseController->getCurrentUser() == null && $action != 'login')
		{
			throw new UnauthorizedException();
		}
	}

	if (array_key_exists($action, $ROUTES))
	{
		try
		{
			// http_response_code(200);
			$reflexionMethod = new ReflectionMethod($ROUTES[$action][0], $ROUTES[$action][1]);
			$reflexionMethod->invoke(new $ROUTES[$action][0], array());
		}
		catch (ReflectionException $e)
		{
			throw new NotFoundException ($e);
		}
	}
	else
	{
		throw new NotFoundException($action . ' not found');
	}
}
catch (BadRequestException $e)
{
	if ($CONFIG['debug'])
	{
		$msg = htmlspecialchars($e->getMessage() . ':<br/>' . $e->getTraceAsString());
	}
	else
	{
		$msg = 'Bad request';
	}
	http_response_code(400);
	$baseController->view('exceptions/exception.php', array('errormsg' => $msg));
}
catch (UnauthorizedException $e)
{
	http_response_code(401);
	//$baseController->view('auth/login.php', array ('wantedurl' => $_SERVER['REQUEST_URI']));
	$baseController->view('auth/login.php', array ('msg' => 'Please login', 'wantedurl' => '?' . $_SERVER['QUERY_STRING']));
}
catch (ForbiddenException $e)
{
	if ($CONFIG['debug'])
	{
		$msg = htmlspecialchars($e->getMessage() . ':<br/>' . $e->getTraceAsString());
	}
	else
	{
		$msg = 'Access denied';
	}
	http_response_code(403);
	$baseController->view('exceptions/exception.php', array('errormsg' => $msg));
}
catch (NotFoundException $e)
{
	if ($CONFIG['debug'])
	{
		$msg = htmlspecialchars($e->getMessage() . ':<br/>' . $e->getTraceAsString());
	}
	else
	{
		$msg = 'Not found';
	}
	http_response_code(404);
	$baseController->view('exceptions/exception.php', array('errormsg' => $msg));
}
// catch (InternalServerErrorException $e)
catch (\Exception $e)
{
	if ($CONFIG['debug'])
	{
		$msg = htmlspecialchars($e->getMessage() . ':<br/>' . $e->getTraceAsString());
	}
	else
	{
		$msg = 'Internal error';
	}
	http_response_code(500);
	$baseController->view('exceptions/exception.php', array('errormsg' => $msg));
}
finally
{
	// Do nothing
}

?>
