<?php
namespace chsxf\MFX\Attributes;

use Attribute;
use chsxf\MFX\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class RequiredRequestMethod extends AbstractRouteStringAttribute
{
    public function __construct(RequestMethod $_requestMethod) {
        parent::__construct($_requestMethod->name);
    }
}
