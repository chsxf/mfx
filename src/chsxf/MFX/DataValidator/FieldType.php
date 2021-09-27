<?php
/**
 * Data validation field type enum class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

use chsxf\MFX\Enum;

/**
 * Enum of all available data validation types
 * 
 * @see Enum
 */
final class FieldType extends Enum
{
	const __default = self::TEXT;
	
	// Built-in HTML input types
	const CHECKBOX = 'checkbox';
	const COLOR = 'color';
	const DATE = 'date';
	const EMAIL = 'email';
	const FILE = 'file';
	const HIDDEN = 'hidden';
	const MONTH = 'month';
	const NUMBER = 'number';
	const RADIO = 'radio';
	const RANGE = 'range';
	const SELECT = 'select';
	const TEL = 'tel';
	const TEXT = 'text';
	const TIME = 'time';
	const URL = 'url';
	const WEEK = 'week';
	const PASSWORD = 'password';
	
	// Custom type
	const INTEGER = 'integer';
	const MULTI_SELECT = 'multiselect';
	const NEGATIVE_INTEGER = 'neginteger';
	const NEGATIVEZERO_INTEGER = 'negzerointeger';
	const POSITIVE_INTEGER = 'posinteger';
	const POSITIVEZERO_INTEGER = 'poszerointeger';
	const TEXTAREA = 'textarea';
	const LOWERCASE_WORD = 'lower_word';
	const UPPERCASE_WORD = 'upper_word';
	const WORD = 'word';
	
	/**
	 * @var array Type to class map
	 */
	private static array $_classForType = array();
	
	/**
	 * Registers a class name for a specific field type
	 * @param FieldType $type Field type
	 * @param string $className Class name
	 * @throws DataValidatorException If a class is already registered for this type
	 */
	public static function registerClassForType(FieldType $type, string $className) {
        if (array_key_exists($type->value(), self::$_classForType)) {
            throw new DataValidatorException(dgettext('mfx', "A class is already registered for the field type."));
        }
		self::$_classForType[$type->value()] = $className;
	}
	
	/**
	 * Gets the class name registered for a specific type, of the default Field if none is provided
	 * @param FieldType $type Field type
	 * @return string
	 */
	public static function getClassForType(FieldType $type): string {
        if (!array_key_exists($type->value(), self::$_classForType)) {
            return Field::class;
        }
		else {
            return self::$_classForType[$type->value()];
        }
	}
}