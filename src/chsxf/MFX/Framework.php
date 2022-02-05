<?php
namespace chsxf\MFX;

use chsxf\MFX\DataValidator\Twig\Extension;
use chsxf\MFX\L10n\L10nManager;
use chsxf\Twig\Extension\Gettext;
use chsxf\Twig\Extension\Lazy;
use chsxf\Twig\Extension\SwitchCase;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

final class Framework {

    public static function init() {
        CommandLine::handleInvocation();
        
        // Loading configuration
        $configFilePath = defined('chsxf\MFX\CONFIG_FILE_PATH') ? constant('chsxf\MFX\CONFIG_FILE_PATH') : 'application/config/config.php';
        require_once($configFilePath);
        
        self::registerAutoloader();

        SessionManager::start();
        ErrorManager::unfreeze();

        $iniTimezone = ini_get('date.timezone');
        if (Config::has('timezone') || empty($iniTimezone)) {
            date_default_timezone_set(Config::get('timezone', 'UTC'));
        }

        if (Config::get('profiling', false)) {
            CoreProfiler::init();
        }

        $srcPath = dirname(dirname(dirname(__FILE__)));

        L10nManager::init();
        L10nManager::bindTextDomain('mfx', "{$srcPath}/messages");

        // Initializing Twig
        CoreProfiler::pushEvent('Loading Twig');
        $fsLoader = new FilesystemLoader(Config::get('twig.templates', array()));
        $fsLoader->addPath("{$srcPath}/templates", 'mfx');
        $twig = new Environment($fsLoader, [
            'cache' => Config::get('twig.cache', 'tmp/twig_cache'),
            'debug' => true,
            'strict_variables' => true,
            'autoescape' => false
        ]);
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new Lazy());
        $twig->addExtension(new Gettext());
        $twig->addExtension(new SwitchCase());
        $twig->addExtension(new Extension());
        $customTwigExtensions = Config::get('twig.extensions', array());
        foreach ($customTwigExtensions as $ext) {
            $twig->addExtension(new $ext());
        }

        CoreProfiler::pushEvent('Starting session / Authenticating user');
        User::validate();

        CoreProfiler::pushEvent('Processing request');
        CoreManager::handleRequest($twig, Config::get('request.default_route'));

        ErrorManager::freeze();
        CoreProfiler::stop();
    }

    private static function registerAutoloader() {
        // Building autoload directory precedence list
        $__MicroFX_autoload_precedence = Config::get('autoload.precedence', array());
        // -- Ensure we do not have trailing slash (except for root && protocols)
        $__MicroFX_autoload_precedence = array_map(function($entry) {
            if (!preg_match('#^\w+://$#', $entry) && $entry != '/') {
                if (preg_match('#/$#', $entry)) {
                    $entry = substr($entry, 0, -1);
                }
            }
            return $entry;
        }, $__MicroFX_autoload_precedence);

        // Setting include path
        set_include_path(implode(PATH_SEPARATOR, $__MicroFX_autoload_precedence) . PATH_SEPARATOR . get_include_path());
        unset($__MicroFX_autoload_precedence);

        // Setting autoload built-in functions
        spl_autoload_register(function($class) {
            // Removing namespace for framework classes
            $class = preg_replace('/^chsxf\\\\MFX\\\\/', '', $class);
            $class = preg_replace('/(_|\\\\)/', '/', $class);
            $incPath = explode(PATH_SEPARATOR, get_include_path());
            foreach ($incPath as $p) {
                $fp = "{$p}/{$class}.php";
                if (file_exists($fp)) {
                    require_once($fp);
                    break;
                }
            }
        });
    }

}