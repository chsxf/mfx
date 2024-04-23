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
     * @since 1.0
     * @param RequestMethod $_requestMethod 
     */
    public function __construct(RequestMethod $_requestMethod)
    {
        parent::__construct($_requestMethod->name);
    }
}
