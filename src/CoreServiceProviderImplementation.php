<?php

namespace chsxf\MFX;

use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\IAuthenticationService;
use chsxf\MFX\Services\ICoreServiceProvider;
use chsxf\MFX\Services\IDatabaseService;
use chsxf\MFX\Services\IRequestService;
use chsxf\MFX\Services\ITemplateService;
use chsxf\MFX\Services\ILocalizationService;
use chsxf\MFX\Services\IProfilingService;
use chsxf\MFX\Services\IScriptService;
use chsxf\MFX\Services\ISessionService;
use chsxf\MFX\Services\IStyleSheetService;

final class CoreServiceProviderImplementation implements ICoreServiceProvider
{
    public function __construct(
        private readonly IConfigService $configService,
        private readonly IRequestService $requestService,
        private readonly ITemplateService $templateService,
        private readonly ILocalizationService $localizationService,
        private readonly IProfilingService $profilingService,
        private readonly IScriptService $scriptService,
        private readonly IStyleSheetService $styleSheetService,
        private readonly IAuthenticationService $authenticationService,
        private readonly IDatabaseService $databaseService,
        private readonly ISessionService $sessionService
    ) {
    }

    public function getConfigService(): IConfigService
    {
        return $this->configService;
    }

    public function getRequestService(): IRequestService
    {
        return $this->requestService;
    }

    public function getTemplateService(): ITemplateService
    {
        return $this->templateService;
    }

    public function getLocalizationService(): ILocalizationService
    {
        return $this->localizationService;
    }

    public function getProfilingService(): IProfilingService
    {
        return $this->profilingService;
    }

    public function getScriptService(): IScriptService
    {
        return $this->scriptService;
    }

    public function getStyleSheetService(): IStyleSheetService
    {
        return $this->styleSheetService;
    }

    public function getAuthenticationService(): IAuthenticationService
    {
        return $this->authenticationService;
    }

    public function getDatabaseService(): IDatabaseService
    {
        return $this->databaseService;
    }

    public function getSessionService(): ISessionService
    {
        return $this->sessionService;
    }
}
