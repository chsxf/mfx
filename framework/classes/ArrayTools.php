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
}