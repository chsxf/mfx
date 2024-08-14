<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IStyleSheetService;

/**
 * @since 2.0
 * @ignore
 */
final class StyleSheetServiceProxy implements IStyleSheetService
{
    public function __construct(private readonly IStyleSheetService $styleSheetService)
    {
    }

    public function add(string $url, string $media = 'screen', bool $inline = false, bool $prepend = false, string $type = 'text/css'): void
    {
        $this->styleSheetService->add($url, $media, $inline, $prepend, $type);
    }
}
