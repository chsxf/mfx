<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Attributes\RouteAttributesParser;
use ReflectionMethod;

/**
 * @since 1.0
 */
final class RouterData
{
    /**
     * @since 1.0
     * @param string $route
     * @param RouteAttributesParser $routeProviderAttributes
     * @param RouteAttributesParser $routeAttributes
     * @param array $routeParams
     * @param ReflectionMethod $routeMethod
     * @param string $defaultTemplate
     */
    public function __construct(
        public readonly string $route,
        public readonly RouteAttributesParser $routeProviderAttributes,
        public readonly RouteAttributesParser $routeAttributes,
        public readonly array $routeParams,
        public readonly ReflectionMethod $routeMethod,
        public readonly string $defaultTemplate
    ) {
    }
}
