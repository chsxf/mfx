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

define('chsxf\MFX\ROOT', dirname(dirname(__FILE__)));

/**
 * @since 1.0
 */
final class Framework
{
    /**
     * @since 1.0
     * @param string $configFilePath
     */
    public static function init(string $configFilePath)
    {
        CommandLine::handleInvocation();

        // Loading configuration
        require_once($configFilePath);

        SessionManager::start();
        ErrorManager::unfreeze();

        $iniTimezone = ini_get('date.timezone');
        if (Config::has(ConfigConstants::TIMEZONE) || empty($iniTimezone)) {
            date_default_timezone_set(Config::get(ConfigConstants::TIMEZONE, 'UTC'));
        }

        if (Config::get(ConfigConstants::PROFILING, false)) {
            CoreProfiler::init();
        }

        L10nManager::init();
        L10nManager::bindTextDomain('mfx', ROOT . '/messages');

        // Initializing Twig
        CoreProfiler::pushEvent('Loading Twig');
        $fsLoader = new FilesystemLoader(Config::get(ConfigConstants::TWIG_TEMPLATES, array()));
        $fsLoader->addPath(ROOT . '/templates', 'mfx');
        $twig = new Environment($fsLoader, [
            'cache' => Config::get(ConfigConstants::TWIG_CACHE, '../tmp/twig_cache'),
            'debug' => true,
            'strict_variables' => true,
            'autoescape' => false
        ]);
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new Lazy());
        $twig->addExtension(new Gettext());
        $twig->addExtension(new SwitchCase());
        $twig->addExtension(new Extension());
        $customTwigExtensions = Config::get(ConfigConstants::TWIG_EXTENSIONS, array());
        foreach ($customTwigExtensions as $ext) {
            $twig->addExtension(new $ext());
        }

        CoreProfiler::pushEvent('Starting session / Authenticating user');
        User::validate();

        CoreProfiler::pushEvent('Processing request');
        CoreManager::handleRequest($twig, Config::get(ConfigConstants::REQUEST_DEFAULT_ROUTE, 'none'));

        ErrorManager::freeze();
        CoreProfiler::stop();
    }
}
