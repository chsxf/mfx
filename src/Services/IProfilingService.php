<?php

namespace chsxf\MFX\Services;

/**
 * Profiling service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IProfilingService
{
    /**
     * Tells if the the profiling service is enabled or not
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Gets the total profiling duration, or false if the profiler is either not active or profiling is not done
     * @return float|false
     */
    public function getProfilingDuration(): float|false;

    /**
     * Pushes an event into the profiling data
     * @param string $event Event name to push
     */
    public function pushEvent(string $event): void;
}
