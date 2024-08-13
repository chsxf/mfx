<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\ITemplateService;
use Twig\Environment;

/**
 * @since 2.0
 * @ignore
 */
final class TemplateServiceProxy implements ITemplateService
{
    public function __construct(private readonly ITemplateService $templateService)
    {
    }

    public function getTwig(): ?Environment
    {
        return $this->templateService->getTwig();
    }

    public function convertFakeProtocols(string $str): string
    {
        return $this->templateService->convertFakeProtocols($str);
    }
}
