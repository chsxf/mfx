<?php

namespace chsxf\MFX\Services;

interface IProfilingService
{
    public function isActive(): bool;
    public function getProfilingDuration(): float|false;
    public function pushEvent(string $event);
}
