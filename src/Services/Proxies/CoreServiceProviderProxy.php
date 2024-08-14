<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

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

/**
 * Default implementation of a core service provider
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 * @ignore
 */
final class CoreServiceProviderProxy implements ICoreServiceProvider
{
    private readonly ConfigServiceProxy $configServiceProxy;
    private readonly RequestServiceProxy $requestServiceProxy;
    private readonly TemplateServiceProxy $templateServiceProxy;
    private readonly LocalizationServiceProxy $localizationServiceProxy;
    private readonly ProfilingServiceProxy $profilingServiceProxy;
    private readonly ScriptServiceProxy $scriptServiceProxy;
    private readonly StyleSheetServiceProxy $styleSheetServiceProxy;
    private readonly AuthenticationServiceProxy $authenticationServiceProxy;
    private readonly DatabaseServiceProxy $databaseServiceProxy;
    private readonly SessionServiceProxy $sessionServiceProxy;

    public function __construct(
        IConfigService $configService,
        IRequestService $requestService,
        ITemplateService $templateService,
        ILocalizationService $localizationService,
        IProfilingService $profilingService,
        IScriptService $scriptService,
        IStyleSheetService $styleSheetService,
        IAuthenticationService $authenticationService,
        IDatabaseService $databaseService,
        ISessionService $sessionService
    ) {
        $this->configServiceProxy = new ConfigServiceProxy($configService);
        $this->requestServiceProxy = new RequestServiceProxy($requestService);
        $this->templateServiceProxy = new TemplateServiceProxy($templateService);
        $this->localizationServiceProxy = new LocalizationServiceProxy($localizationService);
        $this->profilingServiceProxy = new ProfilingServiceProxy($profilingService);
        $this->scriptServiceProxy = new ScriptServiceProxy($scriptService);
        $this->styleSheetServiceProxy = new StyleSheetServiceProxy($styleSheetService);
        $this->authenticationServiceProxy = new AuthenticationServiceProxy($authenticationService);
        $this->databaseServiceProxy = new DatabaseServiceProxy($databaseService);
        $this->sessionServiceProxy = new SessionServiceProxy($sessionService);
    }

    public function getConfigService(): IConfigService
    {
        return $this->configServiceProxy;
    }

    public function getRequestService(): IRequestService
    {
        return $this->requestServiceProxy;
    }

    public function getTemplateService(): ITemplateService
    {
        return $this->templateServiceProxy;
    }

    public function getLocalizationService(): ILocalizationService
    {
        return $this->localizationServiceProxy;
    }

    public function getProfilingService(): IProfilingService
    {
        return $this->profilingServiceProxy;
    }

    public function getScriptService(): IScriptService
    {
        return $this->scriptServiceProxy;
    }

    public function getStyleSheetService(): IStyleSheetService
    {
        return $this->styleSheetServiceProxy;
    }

    public function getAuthenticationService(): IAuthenticationService
    {
        return $this->authenticationServiceProxy;
    }

    public function getDatabaseService(): IDatabaseService
    {
        return $this->databaseServiceProxy;
    }

    public function getSessionService(): ISessionService
    {
        return $this->sessionServiceProxy;
    }
}
