<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\ILocalizationService;

final class LocalizationServiceProxy implements ILocalizationService
{
    public function __construct(private readonly ILocalizationService $localizationService)
    {
    }

    public function bindTextDomain(string $key, string $path, string $charset = 'UTF-8')
    {
        $this->localizationService->bindTextDomain($key, $path, $charset);
    }

    public function getLocale(): string
    {
        return $this->localizationService->getLocale();
    }

    public function getLanguage(): string
    {
        return $this->localizationService->getLanguage();
    }
}
