<?php

declare(strict_types=1);

namespace chsxf\MFX\Attributes;

/**
 * @since 1.0
 */
abstract class AbstractRouteStringAttribute extends AbstractRouteAttribute
{
    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function __construct(private string $value)
    {
    }
}
