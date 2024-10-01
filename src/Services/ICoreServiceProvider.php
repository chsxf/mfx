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
     * @return IRequestService
     */
    public function getRequestService(): IRequestService;

    /**
     * Gets the current template service
     * @return ITemplateService
     */
    public function getTemplateService(): ITemplateService;

    /**
     * Gets the current localization service
     * @return ILocalizationService
     */
    public function getLocalizationService(): ILocalizationService;

    /**
     * Gets the current profiling service
     * @return IProfilingService
     */
    public function getProfilingService(): IProfilingService;

    /**
     * Gets the current script service
     * @return IScriptService
     */
    public function getScriptService(): IScriptService;

    /**
     * Gets the current stylesheet service
     * @return IStyleSheetService
     */
    public function getStyleSheetService(): IStyleSheetService;

    /**
     * Gets the current authentication service
     * @return IAuthenticationService
     */
    public function getAuthenticationService(): IAuthenticationService;

    /**
     * Gets the current database service
     * @return IDatabaseService
     */
    public function getDatabaseService(): IDatabaseService;

    /**
     * Gets the current session service
     * @return ISessionService
     */
    public function getSessionService(): ISessionService;
}
