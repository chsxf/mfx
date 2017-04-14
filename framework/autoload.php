<?php
/**
 * PHP autoload configuration
 *
 * This framework uses Standard PHP Library (SPL) to handle classes autoloading
 *
 * @version 1.0
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @package framework
 *
 * @license GPL, version 2
 */

// Building autoload directory precedence list
$__MicroFX_autoload_precedence = \CheeseBurgames\MFX\Config::get('autoload_precedence', array());
array_unshift($__MicroFX_autoload_precedence, 
		'mfx/framework/interfaces', 
		'mfx/framework/classes', 
		'mfx/3rd-party',
		'mfx/3rd-party/Twig/lib',
		'mfx/3rd-party/pdo-database-manager/src',
		'mfx/3rd-party/Xhaleera-TwigTools'
);
// -- Ensure we do not have trailing slash (except for root && protocols)
$__MicroFX_autoload_precedence = array_map(function($entry) {
	if (!preg_match('#^\w+://$#', $entry) && $entry != '/')
	{
		if (preg_match('#/$#', $entry))
			$entry = substr($entry, 0, -1);
		if (!preg_match('#^/#', $entry))
			$entry = dirname(dirname(dirname(__FILE__))) . '/' . $entry;
	}
	return $entry;
}, $__MicroFX_autoload_precedence);

// Setting include path
set_include_path(implode(PATH_SEPARATOR, $__MicroFX_autoload_precedence) . PATH_SEPARATOR . get_include_path());
unset($__MicroFX_autoload_precedence);

// Setting autoload built-in functions
spl_autoload_register(function($class) {
	// Removing namespace for framework classes
	$class = preg_replace('/^CheeseBurgames\\\\MFX\\\\', '', $class);
	$class = preg_replace('/(_|\\\\)/', '/', $class);
	$incPath = explode(PATH_SEPARATOR, get_include_path());
	foreach ($incPath as $p)
	{
		$fp = "{$p}/{$class}.php";
		if (file_exists($fp))
		{
			require_once($fp);
			break;
		}
	}
});
