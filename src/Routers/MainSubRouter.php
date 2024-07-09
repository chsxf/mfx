<?php

/**
 * MainSub (RouteProvider.Route) Router Implementation
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\Config;
use chsxf\MFX\ConfigConstants;
use chsxf\MFX\CoreManager;
use ErrorException;
use ReflectionException;

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
     * @throws ErrorException
     * @throws ReflectionException
     */
    public function parseRoute(string $filteredPathInfo, string $defaultRoute): RouterData
    {
        // Guessing route from path info
        if (empty($filteredPathInfo)) {
            if ($defaultRoute == 'none') {
                CoreManager::dieWithStatusCode(404);
            }

            $route = $defaultRoute;
            $routeParams = [];
        } else {
            $chunks = explode('/', $filteredPathInfo, 2);
            $route = $chunks[0];
            $firstRouteParam = 1;
            if (!preg_match(self::ROUTE_REGEXP, $route) && Config::get(ConfigConstants::ROUTER_OPTIONS_ALLOW_DEFAULT_ROUTE_SUBSTITUTION, false)) {
                $route = $defaultRoute;
                $firstRouteParam = 0;
            }
            $routeParams = isset($chunks[$firstRouteParam]) ? explode('/', $chunks[$firstRouteParam]) : [];
        }

        // Checking route
        if (!preg_match(self::ROUTE_REGEXP, $route)) {
            RouterHelpers::check404file($routeParams);
            throw new \ErrorException("'{$route}' is not a valid route.");
        }
        list($providerClassName, $routeMethodName) = explode('.', $route);

        $providerClass = RouterHelpers::getRouteProviderClass($providerClassName);
        if ($providerClass === null) {
            RouterHelpers::check404file($routeParams);
        }
        if ($providerClass === null || !$providerClass->implementsInterface(IRouteProvider::class)) {
            throw new \ErrorException("'{$providerClassName}' is not a valid route provider.");
        }
        $providerAttributes = new RouteAttributesParser($providerClass);

        // Checking sub-route
        $routeMethod = $providerClass->getMethod($routeMethodName);
        $routeAttributes = RouterHelpers::isMethodValidRoute($routeMethod);
        if (false === $routeAttributes) {
            throw new \ErrorException("'{$routeMethodName}' is not a valid route of the '{$providerClassName}' provider.");
        }

        $defaultTemplate = str_replace(array('_', '.'), '/', $route);

        return new RouterData($route, $providerAttributes, $routeAttributes, $routeParams, $routeMethod, $defaultTemplate);
    }
}
