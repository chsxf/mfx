<?php

namespace chsxf\MFX\Attributes;

class RouteAttributesParser
{
    private array $attributes = array();

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

    public function hasAttribute(string $class)
    {
        foreach ($this->attributes as $attr) {
            if ($attr instanceof $class || is_subclass_of($attr, $class, false)) {
                return true;
            }
        }
        return false;
    }

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
