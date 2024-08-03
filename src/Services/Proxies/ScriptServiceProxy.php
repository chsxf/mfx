<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IScriptService;

final class ScriptServiceProxy implements IScriptService
{
    public function __construct(private readonly IScriptService $scriptService)
    {
    }


    public function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript')
    {
        $this->scriptService->add($url, $inline, $prepend, $type);
    }
}
