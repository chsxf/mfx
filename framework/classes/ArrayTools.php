<?php
/**
 * Class and helper functions for array management
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

/**
 * Array management helper class
 */
class ArrayTools
{
	/**
	 * Reverses dimensions of the source array.
	 *
	 * The result array is built using the keys of the contained arrays, each referencing a new array.
	 * Each related row value is then added in the corresponding array.
	 *
	 * This function is useful to convert database rows to HTML form data.
	 *
	 * Note:
	 * It is assumed that all contained arrays use the same keys.
	 *
	 * @param array $store Source array
	 * @return array
	 */
	public static function reverseArrays(array $store) {
		if (empty($store))
			return array();

		$keys = array_keys(reset($store));
		$result = array();
		foreach ($keys as $k)
			$result[$k] = array();
		foreach ($store as $row)
		{
			foreach ($keys as $k)
			{
				if (array_key_exists($k, $result))
					$result[$k][] = $row[$k];
				else
					$result[$k][] = NULL;
			}
		}
		return $result;
	}

	/**
	 * Concatenate values together into a new array without considering keys or types.
	 *
	 * The function accepts unlimited arguments.
	 * However, if a single array argument is passed, it is used as an array of arguments, thus its content will be concatenated and the array itself.
	 *
	 * @return array
	 */
	public static function concatArrays() {
		$result = array();

		$args = func_get_args();
		if (count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}

		foreach ($args as $arg) {
			if (is_array($arg)) {
				$result = array_merge($result, $arg);
			}
			else {
				$result[] = $arg;
			}
		}

		return $result;
	}
}