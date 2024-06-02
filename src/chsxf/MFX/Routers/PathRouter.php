<?php

/**
 * PathRouter (route_provider/route) Router Implementation
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\CoreManager;

/**
 * @since 1.0
 */
class PathRouter implements IRouter
{
    private const ROUTE_REGEXP = '#^[[:alnum:]_]+/[[:alnum:]_]+?$#';

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
        if (empty($filteredPathInfo)) {
            if ($defaultRoute === 'none') {
                CoreManager::dieWithStatusCode(404);
            }

            $route = $defaultRoute;
            $routeParams = [];
        } else {
            $chunks = explode('/', $filteredPathInfo);
            if (count($chunks) < 2) {
                if ($defaultRoute === 'none') {
                    CoreManager::dieWithStatusCode(404);
                } else {
                    $route = $defaultRoute;
                    $routeParams = $chunks;
                }
            } else {
                $route = "{$chunks[0]}/{$chunks[1]}";
                $routeParams = array_slice($chunks, 2);
            }
        }

        // Checking route
        if (!preg_match(self::ROUTE_REGEXP, $route)) {
            RouterHelpers::check404file($routeParams);
            throw new \ErrorException("'{$route}' is not a valid route.");
        }
        list($providerClassName, $routeMethodName) = explode('/', $route);

        $providerClass = RouterHelpers::getRouteProviderClass($providerClassName);
        if ($providerClass === NULL) {
            RouterHelpers::check404file($routeParams);
        }
        if ($providerClass === NULL || !$providerClass->implementsInterface(IRouteProvider::class)) {
            throw new \ErrorException("'{$providerClassName}' is not a valid route provider.");
        }
        $providerAttributes = new RouteAttributesParser($providerClass);

        // Checking sub-route
        $routeMethod = $providerClass->getMethod($routeMethodName);
        $routeAttributes = RouterHelpers::isMethodValidRoute($routeMethod);
        if (false === $routeAttributes) {
            throw new \ErrorException("'{$routeMethodName}' is not a valid route of the '{$providerClassName}' provider.");
        }

        $defaultTemplate = "{$providerClass->getName()}/{$routeMethod->getName()}";
        return new RouterData($route, $providerAttributes, $routeAttributes, $routeParams, $routeMethod, $defaultTemplate);
    }
}
