<?php
namespace chsxf\MFX\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequiredContentTypeAttribute extends AbstractRouteStringAttribute
{
    
}