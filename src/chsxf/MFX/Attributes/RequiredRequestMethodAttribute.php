<?php
namespace chsxf\MFX\Attributes;

use Error;
use ReflectionClass;
use ReflectionClassConstant;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequiredRequestMethodAttribute extends AbstractRouteStringAttribute
{
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const CONNECT = 'CONNECT';
    public const OPTIONS = 'OPTIONS';
    public const TRACE = 'TRACE';
    public const PATCH = 'PATCH';

    private static ?array $classConstants = NULL;

    public function __construct(string $value) {
        if (self::$classConstants === null) {
            self::$classConstants = array();

            $rc = new ReflectionClass(__CLASS__);
            $constants = $rc->getConstants(ReflectionClassConstant::IS_PUBLIC);
            foreach ($constants as $constant) {
                self::$classConstants[] = $constant->getValue();
            }
        }

        if (!in_array($value, self::$classConstants, true)) {
            throw new Error("Invalid request method '{$value}'");
        }

        parent::__construct($value);
    }
}
