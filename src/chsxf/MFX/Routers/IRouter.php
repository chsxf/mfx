<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

interface IRouter
{
    public function parseRoute(string $filteredPathInfo, string $defaultRoute): RouterData;
}
