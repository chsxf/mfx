<?php
/**
 * JSON tools
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Class containing utility functions for encoding data in JSON
 */
class JSONTools
{
	/**
	 * @var string Stores the string representation of PHP_INT_MAX
	 */
	private static $PHP_INT_MAX_AS_STR = NULL;
	/**
	 * @var int Stores the string representation length of PHP_INT_MAX 
	 */
	private static $PHP_INT_MAX_LENGTH = 0;
	/**
	 * @var array Container for object references used to avoid recursions
	 */		
	private static $RECURSIONS;
	
	/**
	 * Filter string values recursively in order to use the proper primitive type if applying.
	 * 
	 * For objects, the function proceeds to a clone and iterates only on public properties. 
	 *  
	 * @param mixed $var Value to filter
	 * @return mixed The filtered value, or the original value if no filtering is applying
	 * 
	 * @used-by JSONTools::filterAndEncode()
	 */
	private static function _filter($var)
	{
		// Scalar values
		if (is_scalar($var))
		{
			if (is_string($var))
			{
				$regs = NULL;
				
				// Booleans as string
				if (preg_match('/^(true|false)$/', $var))
					return ($var === 'true');
				// Integers as string
				else if (preg_match('/^-?([1-9]\d*)$/', $var, $regs))
				{
					if (self::$PHP_INT_MAX_AS_STR === NULL)
					{
						self::$PHP_INT_MAX_AS_STR = strval(PHP_INT_MAX);
						self::$PHP_INT_MAX_LENGTH = strlen(self::$PHP_INT_MAX_AS_STR);
					}
					
					$length = strlen($regs[1]);
					if ($length < self::$PHP_INT_MAX_LENGTH || ($length == self::$PHP_INT_MAX_LENGTH && strcmp(self::$PHP_INT_MAX_AS_STR, $regs[1]) >= 0))
						return intval($var);
					else
						return $var;
				}
				else if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $var))
					return floatval($var);
				else
					return $var;
			}
			else
				return $var;
		}
		// Arrays
		else if (is_array($var))
		{
			foreach ($var as $k => $v)
				$var[$k] = self::_filter($v);
			return $var;
		}
		// Objects
		else if (is_object($var))
		{
			// Check for recursions
			if (in_array($var, self::$RECURSIONS))
				return '#RECURSION#';
			self::$RECURSIONS[] = $var;
			
			if ($var instanceof IUnfilteredSerializable == false)
			{
				$newObj = clone $var;
				$ro = new \ReflectionObject($newObj);
				$props = $ro->getProperties(\ReflectionProperty::IS_PUBLIC);
				foreach ($props as $v)
				{
					if ($v->isDefault())
						$v->setValue($newObj, self::_filter($v->getValue($newObj)));
					else
					{
						$name = $v->getName();
						$newObj->$name = self::_filter($newObj->$name);
					}
				}
				$res = $newObj;
			}
			else
				$res = $var;
			
			array_pop(self::$RECURSIONS);
			return $res;
		}
		// Other
		else
			return $var;
	}
	
	/**
	 * Filter the specified value and encode it in JSON
	 * @param mixed $var Value to filter and encode
	 * @return string A JSON-encoded string representing the specified value
	 * 
	 * @uses JSONTools::filter() to filter the value
	 */
	public static function filterAndEncode($var)
	{
		self::$RECURSIONS = array();
		return json_encode(self::_filter($var));
	}
}