<?php

/**
 * MainSub (RouteProvider.Route) Router Implementation
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Config;
use chsxf\MFX\ConfigConstants;
use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\HttpStatusCodes;
use chsxf\MFX\Services\ICoreServiceProvider;

/**
 * @since 1.0
 */
class MainSubRouter implements IRouter
{
    private const ROUTE_REGEXP = '/^[[:alnum:]_]+\.[[:alnum:]_]+?$/';

    /**
     * @since 1.0
     * @param string $filteredPathInfo
     * @param string $defaultRoute
     * @return RouterData
     * @throws MFXException
     */
    public function parseRoute(ICoreServiceProvider $coreServiceProvider, string $filteredPathInfo, string $defaultRoute): RouterData
    {
        // Guessing route from path info
        if (empty($filteredPathInfo)) {
            if ($defaultRoute == 'none') {
                throw new MFXException(HttpStatusCodes::notFound);
            }

            $route = $defaultRoute;
            $routeParams = [];
        } else {
            $chunks = explode('/', $filteredPathInfo, 2);
            $route = $chunks[0];
            $firstRouteParam = 1;
            if (!preg_match(self::ROUTE_REGEXP, $route) && $coreServiceProvider->getConfigService()->getValue(ConfigConstants::ROUTER_OPTIONS_ALLOW_DEFAULT_ROUTE_SUBSTITUTION, false)) {
                $route = $defaultRoute;
                $firstRouteParam = 0;
            }
            $routeParams = isset($chunks[$firstRouteParam]) ? explode('/', $chunks[$firstRouteParam]) : [];
        }

        // Checking route
        if (!preg_match(self::ROUTE_REGEXP, $route)) {
            RouterHelpers::check404file($routeParams);
            throw new MFXException(message: "'{$route}' is not a valid route.");
        }
        list($providerClassName, $routeMethodName) = explode('.', $route);

        return RouterData::create($coreServiceProvider, $route, $routeParams, $providerClassName, $routeMethodName);
    }
}
