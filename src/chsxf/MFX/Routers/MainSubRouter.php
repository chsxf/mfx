<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\ArrayTools;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\Config;
use chsxf\MFX\CoreManager;

class MainSubRouter implements IRouter
{
    private const ROUTE_REGEXP = '/^[[:alnum:]_]+\.[[:alnum:]_]+?$/';

    public function parseRoute(string $filteredPathInfo, string $defaultRoute): RouterData
    {
        // Guessing route from path info
        if (empty($filteredPathInfo)) {
            if ($defaultRoute == 'none') {
                CoreManager::dieWithStatusCode(200);
            }

            $route = $defaultRoute;
            $routeParams = array();
        } else {
            $chunks = explode('/', $filteredPathInfo, 2);
            $route = $chunks[0];
            $firstRouteParam = 1;
            if (!preg_match(self::ROUTE_REGEXP, $route) && Config::get('router.options.allow_default_route_substitution', false)) {
                $route = $defaultRoute;
                $firstRouteParam = 0;
            }
            $routeParams = (empty($chunks[$firstRouteParam]) && (!isset($chunks[$firstRouteParam]) || $chunks[$firstRouteParam] !== '0')) ? array() : explode('/', $chunks[$firstRouteParam]);
        }

        // Checking route
        if (!preg_match(self::ROUTE_REGEXP, $route)) {
            self::check404file($routeParams);
            throw new \ErrorException("'{$route}' is not a valid route.");
        }
        list($providerClass, $routeMethod) = explode('.', $route);
        try {
            $rc = new \ReflectionClass("\\{$providerClass}");
        } catch (\ReflectionException $e) {
            try {
                $rc = new \ReflectionClass(__NAMESPACE__ . "\\{$providerClass}");
            } catch (\ReflectionException $e) {
                self::check404file($routeParams);
                throw $e;
            }
        }
        if (!$rc->implementsInterface(IRouteProvider::class)) {
            throw new \ErrorException("'{$providerClass}' is not a valid route provider.");
        }
        $providerAttributes = new RouteAttributesParser($rc);

        // Checking subroute
        $routeMethod = $rc->getMethod($routeMethod);
        $routeAttributes = self::isMethodValidRoute($routeMethod);
        if (false === $routeAttributes) {
            throw new \ErrorException("'{$routeMethod}' is not a valid route of the '{$providerClass}' provider.");
        }

        $defaultTemplate = str_replace(array('_', '.'), '/', $route);

        return new RouterData($route, $providerAttributes, $routeAttributes, $routeParams, $routeMethod, $defaultTemplate);
    }

    /**
     * Checks if the request could be referring to a missing file and replies a 404 HTTP error code
     * @param array $routeParams Request route parameters
     */
    private static function check404file(array $routeParams)
    {
        if (!empty($routeParams) && preg_match('/\.[a-z0-9]+$/i', $routeParams[count($routeParams) - 1])) {
            CoreManager::dieWithStatusCode(404);
        }
    }

    /**
     * Checks if a specific method is a valid sub-route
     * @param \ReflectionMethod $method Method to inspect
     * @return RouteAttributesParser|false The route's attributes parser or false in case of an error
     */
    private static function isMethodValidRoute(\ReflectionMethod $method): RouteAttributesParser|false
    {
        // Checking method
        $params = $method->getParameters();
        if (!$method->isStatic() || !$method->isPublic() || (count($params) >= 1 && !ArrayTools::isParameterArray($params[0]))) {
            return false;
        }
        // Building parameters from doc comment
        $routeParser = new RouteAttributesParser($method);
        if (!$routeParser->hasAttribute(Route::class)) {
            return false;
        }
        return $routeParser;
    }
}