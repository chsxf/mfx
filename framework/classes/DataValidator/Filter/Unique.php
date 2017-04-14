<?php
/**
 * Data validation unique values filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filters;

use CheeseBurgames\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating when a repeatable field contains only unique values.
 */
class Unique extends AbstractFilter
{
	/**
	 * Constructor
	 * @param string $message Error message
	 */
	public function __construct($message = NULL)
	{
		if (empty($message))
			$message = dgettext('mfx', "The field '%s' must contain unique values.");
		parent::__construct($message);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::appliesToField()
	 */
	public function appliesToField() {
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field name
	 * @param mixed $value Value to validate
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * 
	 * Note:
	 * The $atIndex parameter is ignored for filters returning true in appliesToField().
	 */
	public function validate($fieldName, $value, $atIndex = NULL) {
		if (!is_array($value))
			return true;
		
		$value = array_filter($value, function($item) {
			return ($item !== NULL && $item !== '');
		});
		if (empty($value))
			return true;
		$cv = array_count_values($value);
		if (max($cv) != 1)
		{
			$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
}