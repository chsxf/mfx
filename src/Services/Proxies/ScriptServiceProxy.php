<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IScriptService;

/**
 * @since 2.0
 * @ignore
 */
final class ScriptServiceProxy implements IScriptService
{
    public function __construct(private readonly IScriptService $scriptService)
    {
    }


    public function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript'): void
    {
        $this->scriptService->add($url, $inline, $prepend, $type);
    }
}
