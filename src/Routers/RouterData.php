<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\ArrayTools;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\ConfigConstants;
use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\RequestResult;
use chsxf\MFX\Routers\RouteAttributesParser;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\ICoreServiceProvider;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @since 1.0
 */
final class RouterData
{
    /**
     * Constructor
     * @since 2.0
     * @param string $route
     * @param RouteAttributesParser $routeProviderAttributes
     * @param RouteAttributesParser $routeAttributes
     * @param array $routeParams
     * @param ReflectionMethod $routeMethod
     * @param string $defaultTemplate
     */
    private function __construct(
        private readonly ICoreServiceProvider $coreServiceProvider,
        public readonly string $route,
        public readonly RouteAttributesParser $routeProviderAttributes,
        public readonly RouteAttributesParser $routeAttributes,
        public readonly array $routeParams,
        private readonly ReflectionClass $providerClass,
        private readonly ReflectionMethod $routeMethod,
        public readonly string $defaultTemplate
    ) {
    }

    public function getResult(): RequestResult
    {
        if ($this->routeMethod->isStatic()) {
            $instance = null;
        } elseif ($this->providerClass->isSubclassOf(BaseRouteProvider::class)) {
            $instance = $this->providerClass->newInstance($this->coreServiceProvider);
        } else {
            $instance = $this->providerClass->newInstance();
        }
        return $this->routeMethod->invoke($instance, $this->routeParams);
    }

    /**
     * Create a new RouterData instance
     * @since 2.0
     * @param ICoreServiceProvider $coreServiceProvider Core service provider instance
     * @param string $route Parsed route name
     * @param array $routeParams Route parameters
     * @param string $providerClassName Route provider class name
     * @param string $routeMethodName Route method to invoke
     * @return RouterData
     * @throws MFXException
     * @throws ReflectionException
     */
    public static function create(ICoreServiceProvider $coreServiceProvider, string $route, array $routeParams, string $providerClassName, string $routeMethodName): RouterData
    {
        $providerClass = self::getRouteProviderClass($coreServiceProvider->getConfigService(), $providerClassName);
        if ($providerClass === null) {
            RouterHelpers::check404file($routeParams);
        }
        if ($providerClass === null || !$providerClass->implementsInterface(IRouteProvider::class)) {
            throw new MFXException(message: "'{$providerClassName}' is not a valid route provider.");
        }
        $providerAttributes = new RouteAttributesParser($providerClass);

        // Checking sub-route
        $routeMethod = $providerClass->getMethod($routeMethodName);
        $routeAttributes = self::isMethodValidRoute($routeMethod);
        if (false === $routeAttributes) {
            throw new MFXException(message: "'{$routeMethodName}' is not a valid route of the '{$providerClassName}' provider.");
        }

        $defaultTemplate = str_replace(array('_', '.'), '/', $route);
        return new RouterData($coreServiceProvider, $route, $providerAttributes, $routeAttributes, $routeParams, $providerClass, $routeMethod, $defaultTemplate);
    }

    private static function getRouteProviderClass(IConfigService $configService, string $providerClassName): ?\ReflectionClass
    {
        $routeNamespaces = $configService->getValue(ConfigConstants::ROUTER_OPTIONS_ALLOWED_NAMESPACES, []);
        if (!in_array('chsxf\\MFX', $routeNamespaces)) {
            array_unshift($routeNamespaces, 'chsxf\\MFX');
        }
        if (!in_array('', $routeNamespaces)) {
            array_unshift($routeNamespaces, '');
        }

        $providerClass = null;
        foreach ($routeNamespaces as $namespace) {
            $namespace = rtrim($namespace, '\\');
            $qualifiedClassName = empty($namespace) ? "\\{$providerClassName}" : "\\{$namespace}\\{$providerClassName}";

            try {
                $providerClass = new \ReflectionClass($qualifiedClassName);
                break;
            } catch (\ReflectionException $e) {
                $providerClass = null;
            }
        }

        return $providerClass;
    }

    private static function isMethodValidRoute(\ReflectionMethod $method): RouteAttributesParser|false
    {
        // Checking method
        $params = $method->getParameters();
        if (!$method->isPublic() || (count($params) >= 1 && !ArrayTools::isParameterArray($params[0]))) {
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
