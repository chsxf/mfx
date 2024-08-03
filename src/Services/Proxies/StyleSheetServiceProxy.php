<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IStyleSheetService;

final class StyleSheetServiceProxy implements IStyleSheetService
{
    public function __construct(private readonly IStyleSheetService $styleSheetService)
    {
    }

    public function add(string $url, string $media = 'screen', bool $inline = false, bool $prepend = false, string $type = 'text/css')
    {
        $this->styleSheetService->add($url, $media, $inline, $prepend, $type);
    }
}
