<?php

namespace chsxf\MFX\Attributes;

use Attribute;

/**
 * @since 1.0
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RedirectURL extends AbstractRouteStringAttribute
{
}
