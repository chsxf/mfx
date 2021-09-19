<?php
/**
 * Main framework source file
 *
 * @version 1.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

use \chsxf\MFX\Config;
use \chsxf\MFX\CoreManager;
use \chsxf\MFX\CoreProfiler;
use \chsxf\MFX\ErrorManager;
use \chsxf\MFX\SessionManager;
use \chsxf\MFX\User;
use \chsxf\MFX\L10n\L10nManager;

define('MFX_ROOT', dirname(__FILE__));

// Minimal version of PHP
define('MFX_REQUIRED_PHP_VERSION', '5.4');
if (version_compare(PHP_VERSION, MFX_REQUIRED_PHP_VERSION) < 0)
	die(sprintf("PHP %s or later version is required.", MFX_REQUIRED_PHP_VERSION));

// Command-line interface invocation handling (if applicable)
require_once('classes/CommandLine.php');
\chsxf\MFX\CommandLine::handleInvocation();

// Loading configuration
require_once('classes/Config.php');
require_once(defined('MFX_CONFIG_FILE_PATH') ? MFX_CONFIG_FILE_PATH : 'application/config/config.php');

// Initializing class auto-loading
require_once 'autoload.php';
require_once 'error_functions.php';

// Starting session and unfreezing error manager
SessionManager::start();
ErrorManager::unfreeze();

// Setting timezone
$iniTimezone = ini_get('date.timezone');
if (Config::has('timezone') || empty($iniTimezone))
	date_default_timezone_set(Config::get('timezone', 'UTC'));

// Enabling profiling if requested
if (Config::get('profiling', false))
	CoreProfiler::init();

// Setting locale
L10nManager::init();
L10nManager::bindTextDomain('mfx', dirname(__FILE__).'/messages');

// Initializing Twig
CoreProfiler::pushEvent('Loading Twig');
$fsLoader = new Twig_Loader_Filesystem(Config::get('twig.templates', array()));
$fsLoader->addPath(dirname(__FILE__) . '/templates', 'mfx');
$loader = new Twig_Loader_Chain(array($fsLoader, new Twig_Loader_String()));
$twig = new Twig_Environment($loader, array(
	'cache' => Config::get('twig.cache', 'tmp/twig_cache'),
	'debug' => true,
	'strict_variables' => true,
	'autoescape' => false
));
$twig->addExtension(new Twig_Extension_Debug());
$twig->addExtension(new Xhaleera_Twig_Extension_Lazy());
$twig->addExtension(new Xhaleera_Twig_Extension_Gettext());
$twig->addExtension(new Xhaleera_Twig_Extension_Switch());
$twig->addExtension(new \chsxf\MFX\DataValidator\Twig\Extension());
$customTwigExtensions = Config::get('twig.extensions', array());
foreach ($customTwigExtensions as $ext)
	$twig->addExtension(new $ext());

// Starting session and authenticating user
CoreProfiler::pushEvent('Starting session / Authenticating user');
User::validate();

// Processing request
CoreProfiler::pushEvent('Processing request');
if (Config::get('doccommentparser.class'))
{
	$class = Config::get('doccommentparser.class');
	CoreManager::setDocCommentParser(new $class());
}
CoreManager::handleRequest($twig, Config::get('request.default_route'));

// Freezing error manager
ErrorManager::freeze();

// Terminating profiling
CoreProfiler::stop();
