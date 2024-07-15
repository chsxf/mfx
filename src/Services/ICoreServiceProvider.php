<?php

namespace chsxf\MFX\Services;

interface ICoreServiceProvider
{
    function getConfigService(): IConfigService;
    function getRequestService(): IRequestService;
    function getTemplateService(): ITemplateService;
    function getLocalizationService(): ILocalizationService;
    function getProfilingService(): IProfilingService;
    function getScriptService(): IScriptService;
    function getStyleSheetService(): IStyleSheetService;
    function getAuthenticationService(): IAuthenticationService;
    function getDatabaseService(): IDatabaseService;
}
