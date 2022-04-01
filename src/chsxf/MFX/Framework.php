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

final class Framework
{
    public static function init(string $configFilePath)
    {
        CommandLine::handleInvocation();

        // Loading configuration
        require_once($configFilePath);

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
        CoreManager::handleRequest($twig, Config::get('request.default_route', 'none'));

        ErrorManager::freeze();
        CoreProfiler::stop();
    }
}
