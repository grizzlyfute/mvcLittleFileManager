<?php

// The path where app setting is located
define('APPDATAPATH',  $_ENV['HOME']  . DIRECTORY_SEPARATOR . 'mvcLittleFileManager/config' . DIRECTORY_SEPARATOR);
if (APPDATAPATH == '') die ('Path to config data directory is not defined. Set it in ' . __FILE__);
$CONFIG = array();

// $global configuration
$CONFIG_DEFAULT = array
(
	'apptitle' => 'App title',
	'rootdirectory' => APPDATAPATH . 'data',
	'timezone' => 'Etc/UTC',
	'debug' => false, // set to false to not reporting error
	'dateformat' => 'Y-m-d h:i:s',
	'lang' => 'en',
	'highlightjs_style' => 'default',
	'maxuploadsize' => 10000000,
	'useauth' => false,
	'onlineviewer' => 'none',
	'exclude_items' => array(),
	'show_hidden_files' => true,
	'rememberme_ts' => 180*24*60*60,
	'tmppath' => sys_get_temp_dir(),
	'max_size_to_compress' => 50000000,
	'thumbnail' => false,
);

function load_config()
{
	global $CONFIG, $CONFIG_DEFAULT;
	$confTemp = array();
	if (file_exists(APPDATAPATH . 'config.json'))
	{
		$confTemp = json_decode(file_get_contents(APPDATAPATH . 'config.json'), true);
		if ($confTemp === null) $confTemp = array();
	}

	foreach ($CONFIG_DEFAULT as $key => $value)
	{
		if (array_key_exists($key, $confTemp))
		{
			$CONFIG[$key] = $confTemp[$key];
		}
		else
		{
			$CONFIG[$key] = $CONFIG_DEFAULT[$key];
		}
	}
}

function save_config(array $newConfig): bool
{
	$ret = false;
	global $CONFIG, $CONFIG_DEFAULT;
	$CONFIG = array();

	// Fill default value
	foreach ($CONFIG_DEFAULT as $key => $value)
	{
		if (array_key_exists($key, $newConfig))
		{
			$CONFIG[$key] = $newConfig[$key];
		}
		else
		{
			$CONFIG[$key] = $CONFIG_DEFAULT[$key];
		}
	}

	if (file_put_contents(APPDATAPATH . 'config.json', json_encode($CONFIG, JSON_PRETTY_PRINT)) === false)
	{
		$ret = false;
	}
	else
	{
		$ret = true;
	}
	// Ensure no error by reloading
	load_config();
	return $ret;
}

?>
