<?php
namespace chsxf\MFX\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RedirectURI extends AbstractRouteStringAttribute
{
    
}