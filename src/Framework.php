<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\L10n\L10nManager;

define('chsxf\MFX\ROOT', dirname(dirname(__FILE__)));

/**
 * @since 1.0
 */
final class Framework
{
    private static bool $initialized = false;

    /**
     * @since 1.0
     * @param string $configFilePath
     */
    public static function init(string $configFilePath)
    {
        if (self::$initialized) {
            throw new MFXException(HttpStatusCodes::internalServerError, "MFX has already been initialized");
        }
        self::$initialized = true;

        $argumentsFromCLI = CommandLine::handleInvocation();
        if ($argumentsFromCLI === null) {
            $route = $_SERVER['REQUEST_URI'];
        } else {
            list($configFilePathFromCLI, $routeFromCLI) = $argumentsFromCLI;
            if ($configFilePathFromCLI != null) {
                $configFilePath = $configFilePathFromCLI;
            }
            $route = $routeFromCLI;
        }

        // Loading configuration
        $configManager = new ConfigManager();
        $configData = require_once($configFilePath);
        $configManager->load($configData);

        $databaseManager = new DatabaseManager($configManager);

        $profiler = new CoreProfiler($configManager->getValue(ConfigConstants::PROFILING, false));

        self::startSession($configManager);
        $errorManager = new ErrorManager($configManager);

        $profiler->pushEvent('Starting session / Authenticating user');
        $userManager = new UserManager($configManager, $databaseManager);

        $localizationService = new L10nManager($configManager);
        $localizationService->bindTextDomain('mfx', ROOT . '/messages');

        $coreManager = new CoreManager($errorManager, $configManager, $localizationService, $profiler, $userManager, $databaseManager);
        set_exception_handler($coreManager->exceptionHandler(...));

        $iniTimezone = ini_get('date.timezone');
        if ($configManager->hasValue(ConfigConstants::TIMEZONE) || empty($iniTimezone)) {
            date_default_timezone_set($configManager->getValue(ConfigConstants::TIMEZONE, 'UTC'));
        }

        $profiler->pushEvent('Processing request');
        $coreManager->handleRequest($route, $configManager->getValue(ConfigConstants::REQUEST_DEFAULT_ROUTE, 'none'));

        $errorManager->freeze();
        if ($profiler->isActive()) {
            $profiler->stop($coreManager->getTwig());
        }
    }

    private static function startSession(ConfigManager $configManager)
    {
        if (empty($configManager->getValue(ConfigConstants::SESSION_ENABLED, true))) {
            return;
        }

        // Setting session parameters
        session_name($configManager->getValue(ConfigConstants::SESSION_NAME, 'MFXSESSION'));
        if ($configManager->getValue(ConfigConstants::SESSION_USE_COOKIES, true)) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_trans_id', '0');
            session_set_cookie_params($configManager->getValue(ConfigConstants::SESSION_LIFETIME, 0), $configManager->getValue(ConfigConstants::SESSION_PATH, ''), $configManager->getValue(ConfigConstants::SESSION_DOMAIN, ''));
            session_start();
        } else {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_trans_id', '1');
            if (!empty($_REQUEST[session_name()])) {
                session_id($_REQUEST[session_name()]);
            }
            session_start();
            output_add_rewrite_var(session_name(), session_id());
        }
    }
}
