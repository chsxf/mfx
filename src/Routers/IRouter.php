<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Services\ICoreServiceProvider;

/**
 * @since 1.0
 */
interface IRouter
{
    /**
     * @since 1.0
     * @param string $filteredPathInfo
     * @param string $defaultRoute
     * @return RouterData
     */
    function parseRoute(ICoreServiceProvider $coreServiceProvider, string $filteredPathInfo, string $defaultRoute): RouterData;
}
