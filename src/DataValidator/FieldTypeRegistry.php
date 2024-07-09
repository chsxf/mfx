<?php

namespace chsxf\MFX\DataValidator;

/**
 * @since 1.0
 */
final class FieldTypeRegistry
{
    /**
     * @var array Type to class map
     */
    private static array $classForType = array();

    /**
     * Registers a class name for a specific field type
     * @since 1.0
     * @param FieldType $type Field type
     * @param string $className Class name
     * @throws DataValidatorException If a class is already registered for this type
     */
    public static function registerClassForType(FieldType $type, string $className)
    {
        if (array_key_exists($type->value, self::$classForType)) {
            throw new DataValidatorException(dgettext('mfx', "A class is already registered for the field type."));
        }
        self::$classForType[$type->value] = $className;
    }

    /**
     * Gets the class name registered for a specific type, of the default Field if none is provided
     * @ignore
     * @param FieldType $type Field type
     * @return string
     */
    public static function getClassForType(FieldType $type): string
    {
        if (!array_key_exists($type->value, self::$classForType)) {
            return Field::class;
        } else {
            return self::$classForType[$type->value];
        }
    }
}
