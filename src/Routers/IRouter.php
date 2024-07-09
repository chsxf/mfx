<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

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
    public function parseRoute(string $filteredPathInfo, string $defaultRoute): RouterData;
}
