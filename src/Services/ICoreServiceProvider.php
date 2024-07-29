<?php

namespace chsxf\MFX\Services;

interface ICoreServiceProvider
{
    public function getConfigService(): IConfigService;
    public function getRequestService(): IRequestService;
    public function getTemplateService(): ITemplateService;
    public function getLocalizationService(): ILocalizationService;
    public function getProfilingService(): IProfilingService;
    public function getScriptService(): IScriptService;
    public function getStyleSheetService(): IStyleSheetService;
    public function getAuthenticationService(): IAuthenticationService;
    public function getDatabaseService(): IDatabaseService;
    public function getSessionService(): ISessionService;
}
