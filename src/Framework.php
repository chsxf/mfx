<?php

declare(strict_types=1);

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
     * @param string $configFilePath Path of the config file to load
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

        $sessionManager = new SessionManager($configManager);
        $errorManager = new ErrorManager($configManager, $sessionManager);

        $profiler->pushEvent('Starting session / Authenticating user');
        $userManager = new UserManager($configManager, $databaseManager, $sessionManager);

        $localizationService = new L10nManager($configManager);
        $localizationService->bindTextDomain('mfx', ROOT . '/messages');

        $coreManager = new CoreManager(
            $errorManager,
            $configManager,
            $localizationService,
            $profiler,
            $userManager,
            $databaseManager,
            $sessionManager
        );
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
}
