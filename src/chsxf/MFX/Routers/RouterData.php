<?php

/**
 * Router interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Attributes\RouteAttributesParser;
use ReflectionMethod;

final class RouterData
{
    function __construct(
        public readonly string $route,
        public readonly RouteAttributesParser $routeProviderAttributes,
        public readonly RouteAttributesParser $routeAttributes,
        public readonly array $routeParams,
        public readonly ReflectionMethod $routeMethod,
        public readonly string $defaultTemplate
    ) {
    }
}
