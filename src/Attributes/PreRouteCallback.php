<?php

namespace chsxf\MFX\Attributes;

use Error;
use Attribute;

/**
 * @since 1.0
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class PreRouteCallback extends AbstractRouteStringAttribute
{
    /**
     * @since 1.0
     * @param string $value
     * @throws Error
     */
    public function __construct(string $value)
    {
        if (!is_callable($value)) {
            throw new Error("Invalid callable '{$value}'");
        }

        parent::__construct($value);
    }
}
