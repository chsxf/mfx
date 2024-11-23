<?php

declare(strict_types=1);

namespace chsxf\MFX\Routers;

use chsxf\MFX\Attributes\AbstractRouteAttribute;
use chsxf\MFX\Attributes\AbstractRouteStringAttribute;
use chsxf\MFX\DataValidator\DataValidatorException;
use ReflectionClass;
use ReflectionMethod;

/**
 * @since 1.0
 */
class RouteAttributesParser
{
    private array $attributes = array();

    /**
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
     * @param string $class
     * @param null|string $defaultValue
     * @return null|string
     * @throws DataValidatorException
     */
    public function getAttributeValue(string $class, ?string $defaultValue = null): ?string
    {
        if (!is_subclass_of($class, AbstractRouteStringAttribute::class)) {
            throw new DataValidatorException("Class '{$class}' is not a valid subclass of " . AbstractRouteStringAttribute::class);
        }

        foreach ($this->attributes as $attr) {
            if ($attr instanceof $class || is_subclass_of($attr, $class, false)) {
                return $attr->getValue();
            }
        }
        return $defaultValue;
    }

    /**
     * @param string $class
     * @param string $value
     * @return bool
     */
    public function hasAttributeWithValue(string $class, string $value): bool
    {
        if (!is_subclass_of($class, AbstractRouteStringAttribute::class)) {
            throw new DataValidatorException("Class '{$class}' is not a valid subclass of " . AbstractRouteStringAttribute::class);
        }

        foreach ($this->attributes as $attr) {
            if (($attr instanceof $class || is_subclass_of($attr, $class, false)) && $attr->getValue() == $value) {
                return true;
            }
        }
        return false;
    }
}
