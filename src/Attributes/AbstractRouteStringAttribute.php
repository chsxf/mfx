<?php

namespace chsxf\MFX\Attributes;

/**
 * @since 1.0
 */
abstract class AbstractRouteStringAttribute extends AbstractRouteAttribute
{
    /**
     * @since 1.0
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @since 1.0
     * @param string $value
     */
    public function __construct(private string $value)
    {
    }
}
