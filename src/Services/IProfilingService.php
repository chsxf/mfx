<?php

namespace chsxf\MFX\Services;

interface IProfilingService
{
    function isActive(): bool;
    function getProfilingDuration(): float|false;
    function pushEvent(string $event);
}
