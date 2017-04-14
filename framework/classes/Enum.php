<?php
/**
 * Simulated enum features
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

/**
 * Exception class for invalid provided enum values
 */
class InvalidEnumValueException extends \Exception { }

/**
 * Trait used by Enum subclasses to retrieve suitable values
 */
trait EnumValuesGetter {
	public static function getValues() {
		return self::getConstList(get_class(), false);
	}
}

/**
 * Class simulated enum like features.
 * 
 * This class uses constants to list all possibles enum values.
 * Its design is similar to SplEnum in order to ease transition between both classes.
 * SplEnum is currently only in PECL and this placeholder is provided for easier installations.
 * 
 * You can specify a __default constant in sub-classes to provide a default value for this enum.
 * 
 * @link http://php.net/manual/en/class.splenum.php
 */
class Enum
{
	/**
	 * @var mixed current enum value
	 */
	private $_value;
	
	/**
	 * Constructor
	 * 
	 * @param mixed $initial_value Enum value. If NULL, the enum will use its default value. If no default value is provided, an exception will be thrown.
	 * @param string $strict If set, value check will be made stricly.
	 * @throws InvalidEnumValueException If $initial_value is NULL and no default is provided, or if $initial_value does not match any class constant.
	 */
	public final function __construct($initial_value = NULL, $strict = false) {
		$possibleValues = self::getConstList(get_class($this), true);
		
		if ($initial_value === NULL)
		{
			if (!array_key_exists('__default', $possibleValues))
				throw new InvalidEnumValueException("No default value is defined for this enum.");
			$this->_value = $possibleValues['__default'];
		}
		else
		{
			unset($possibleValues['__default']);
			if (!in_array($initial_value, $possibleValues, $strict))
				throw new InvalidEnumValueException("'{$initial_value}' is not a valid value for this enum.");
			$this->_value = $initial_value;
		}
	}
	
	/**
	 * Gets the list of the constants defined by an Enum sub-class, building the possible enum values.
	 * 
	 * This function may be overridden to filter out some values (the __default constant MUST NOT be filtered out).
	 * 
	 * @param string $class The class name
	 * @param string $include_default If set, the __default constant will be kept in the array.
	 * @return array an associative array whose keys are constant names and values constant values.
	 * 
	 * @link http://www.php.net/manual/en/splenum.getconstlist.php
	 */
	protected static function getConstList($class, $include_default = false) {
		$rc = new \ReflectionClass($class);
		$constants = $rc->getConstants();
		
		if (!$include_default)
			unset($constants['__default']);
		
		return $constants;
	}
	
	/**
	 * Compares this enum with another value.
	 * 
	 * @param mixed $value The other comparison value
	 * @param string $strict If set, values will be strictly compared.
	 * @return boolean true if values match, false either.
	 */
	public final function equals($value, $strict = false) {
		if ($strict)
			return ($this->_value === $value);
		else
			return ($this->_value == $value);
	}
	
	/**
	 * Gets the current enum value
	 * @return mixed
	 */
	public final function value() {
		return $this->_value;
	}
}