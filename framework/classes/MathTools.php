<?php
/**
 * Class and helper functions for math
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

/**
 * Math helper class
 */
final class MathTools {

	/**
	 * Generates a randomly-generated float number
	 * comprised between 0.0 (inclusive) and 1.0 (inclusive)
	 *
	 * @return number
	 */
	public static function randomFloat() {
		return mt_rand() / mt_getrandmax();
	}

}

