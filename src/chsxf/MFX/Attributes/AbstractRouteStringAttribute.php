<?php
namespace chsxf\MFX\Attributes;

abstract class AbstractRouteStringAttribute extends AbstractRouteAttribute
{
    public function getValue(): string {
        return $this->value;
    }

    public function __construct(private string $value) { }
}