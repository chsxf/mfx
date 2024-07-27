<?php

namespace chsxf\MFX\Routers;

use chsxf\MFX\Services\ICoreServiceProvider;

/**
 * Interface routers must implement
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
interface IRouter
{
    /**
     * Parses route data from the provided information
     * @since Z.0
     * @param ICoreServiceProvider $coreServiceProvider Core service provider instance
     * @param string $filteredPathInfo Path info for the request
     * @param string $defaultRoute Default route to use if none provided
     * @return RouterData
     */
    function parseRoute(ICoreServiceProvider $coreServiceProvider, string $filteredPathInfo, string $defaultRoute): RouterData;
}
