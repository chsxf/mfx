<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IProfilingService;

/**
 * @since 2.0
 * @ignore
 */
final class ProfilingServiceProxy implements IProfilingService
{
    public function __construct(private readonly IProfilingService $profilingService)
    {
    }

    public function isActive(): bool
    {
        return $this->profilingService->isActive();
    }

    public function getProfilingDuration(): float|false
    {
        return $this->profilingService->getProfilingDuration();
    }

    public function pushEvent(string $event): void
    {
        $this->profilingService->pushEvent($event);
    }
}
