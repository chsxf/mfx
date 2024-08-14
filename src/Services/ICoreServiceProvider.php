<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

/**
 * Core service provider interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface ICoreServiceProvider
{
    /**
     * Gets the current configuration service
     * @return IConfigService
     */
    public function getConfigService(): IConfigService;

    /**
     * Gets the current request service
     * @return IConfigService
     */
    public function getRequestService(): IRequestService;

    /**
     * Gets the current template service
     * @return IConfigService
     */
    public function getTemplateService(): ITemplateService;

    /**
     * Gets the current localization service
     * @return IConfigService
     */
    public function getLocalizationService(): ILocalizationService;

    /**
     * Gets the current profiling service
     * @return IConfigService
     */
    public function getProfilingService(): IProfilingService;

    /**
     * Gets the current script service
     * @return IConfigService
     */
    public function getScriptService(): IScriptService;

    /**
     * Gets the current stylesheet service
     * @return IConfigService
     */
    public function getStyleSheetService(): IStyleSheetService;

    /**
     * Gets the current authentication service
     * @return IConfigService
     */
    public function getAuthenticationService(): IAuthenticationService;

    /**
     * Gets the current database service
     * @return IConfigService
     */
    public function getDatabaseService(): IDatabaseService;

    /**
     * Gets the current session service
     * @return IConfigService
     */
    public function getSessionService(): ISessionService;
}
