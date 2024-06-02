<?php

namespace chsxf\MFX\Routers;

use chsxf\MFX\ArrayTools;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\Config;
use chsxf\MFX\ConfigConstants;
use chsxf\MFX\CoreManager;

/**
 * @since 1.0
 */
final class RouterHelpers
{
    /**
     * Checks if the request could be referring to a missing file and replies a 404 HTTP error code
     * @param array $routeParams Request route parameters
     * @since 1.0
     */
    public static function check404file(array $routeParams)
    {
        if (!empty($routeParams) && preg_match('/\.[a-z0-9]+$/i', $routeParams[count($routeParams) - 1])) {
            CoreManager::dieWithStatusCode(404);
        }
    }

    /**
     * Checks if a specific method is a valid route
     * @param \ReflectionMethod $method Method to inspect
     * @return RouteAttributesParser|false The route's attributes parser or false in case of an error
     * @since 1.0
     */
    public static function isMethodValidRoute(\ReflectionMethod $method): RouteAttributesParser|false
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

    public static function getRouteProviderClass(string $providerClassName): ?\ReflectionClass
    {
        $routeNamespaces = Config::get(ConfigConstants::ROUTER_OPTIONS_ALLOWED_NAMESPACES, []);
        if (!in_array('chsxf\\MFX', $routeNamespaces)) {
            array_unshift($routeNamespaces, 'chsxf\\MFX');
        }
        if (!in_array('', $routeNamespaces)) {
            array_unshift($routeNamespaces, '');
        }

        $providerClass = NULL;
        foreach ($routeNamespaces as $namespace) {
            $namespace = rtrim($namespace, '\\');
            $qualifiedClassName = empty($namespace) ? "\\{$providerClassName}" : "\\{$namespace}\\{$providerClassName}";

            try {
                $providerClass = new \ReflectionClass($qualifiedClassName);
                break;
            } catch (\ReflectionException $e) {
                $providerClass = NULL;
            }
        }

        return $providerClass;
    }
}
