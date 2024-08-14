<?php

declare(strict_types=1);

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
