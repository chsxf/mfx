<?php

namespace chsxf\MFX\Attributes;

use Attribute;
use chsxf\MFX\RequestMethod;

/**
 * @since 1.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RequiredRequestMethod extends AbstractRouteStringAttribute
{
    /**
     * @param RequestMethod $_requestMethod
     */
    public function __construct(RequestMethod $_requestMethod)
    {
        parent::__construct($_requestMethod->name);
    }
}
