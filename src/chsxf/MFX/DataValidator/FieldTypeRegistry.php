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
	private static array $_classForType = array();

	/**
	 * Registers a class name for a specific field type
	 * @since 1.0
	 * @param FieldType $type Field type
	 * @param string $className Class name
	 * @throws DataValidatorException If a class is already registered for this type
	 */
	public static function registerClassForType(FieldType $type, string $className)
	{
		if (array_key_exists($type->value, self::$_classForType)) {
			throw new DataValidatorException(dgettext('mfx', "A class is already registered for the field type."));
		}
		self::$_classForType[$type->value] = $className;
	}

	/**
	 * Gets the class name registered for a specific type, of the default Field if none is provided
	 * @ignore
	 * @param FieldType $type Field type
	 * @return string
	 */
	public static function getClassForType(FieldType $type): string
	{
		if (!array_key_exists($type->value, self::$_classForType)) {
			return Field::class;
		} else {
			return self::$_classForType[$type->value];
		}
	}
}
