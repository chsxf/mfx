<?php

declare(strict_types=1);

namespace chsxf\MFX\Attributes;

use Attribute;

/**
 * @since 1.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Route extends AbstractRouteAttribute
{
}
