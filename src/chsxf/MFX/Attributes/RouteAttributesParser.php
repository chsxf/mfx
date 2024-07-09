<?php

namespace chsxf\MFX\Attributes;

use ErrorException;
use ReflectionClass;
use ReflectionMethod;

/**
 * @since 1.0
 */
class RouteAttributesParser
{
    private array $attributes = array();

    /**
     * @since 1.0
     * @param ReflectionClass|ReflectionMethod $reflectedElement
     */
    public function __construct(\ReflectionClass|\ReflectionMethod $reflectedElement)
    {
        $baseAttributeClass = new \ReflectionClass(AbstractRouteAttribute::class);

        $reflectedAttributes = $reflectedElement->getAttributes();
        foreach ($reflectedAttributes as $attr) {
            $instance = $attr->newInstance();

            $ro = new \ReflectionObject($instance);
            if ($ro->isSubclassOf($baseAttributeClass)) {
                $this->attributes[] = $instance;
            }
        }
    }

    /**
     * @since 1.0
     * @param string $class
     * @return bool
     */
    public function hasAttribute(string $class)
    {
        foreach ($this->attributes as $attr) {
            if ($attr instanceof $class || is_subclass_of($attr, $class, false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @since 1.0
     * @param string $class
     * @param null|string $defaultValue
     * @return null|string
     * @throws ErrorException
     */
    public function getAttributeValue(string $class, ?string $defaultValue = null): ?string
    {
        if (!is_subclass_of($class, AbstractRouteStringAttribute::class)) {
            throw new \ErrorException("Class '{$class}' is not a valid subclass of " . AbstractRouteStringAttribute::class);
        }

        foreach ($this->attributes as $attr) {
            if ($attr instanceof $class || is_subclass_of($attr, $class, false)) {
                return $attr->getValue();
            }
        }
        return $defaultValue;
    }
}
