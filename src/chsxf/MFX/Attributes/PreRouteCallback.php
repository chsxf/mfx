<?php

namespace chsxf\MFX\Attributes;

use Error;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class PreRouteCallback extends AbstractRouteStringAttribute
{
    public function __construct(string $value)
    {
        if (!is_callable($value)) {
            throw new Error("Invalid callable '{$value}'");
        }

        parent::__construct($value);
    }
}
