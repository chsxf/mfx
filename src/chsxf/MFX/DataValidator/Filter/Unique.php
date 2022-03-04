<?php

/**
 * Data validation unique values filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating when a repeatable field contains only unique values.
 */
class Unique extends AbstractFilter
{
	/**
	 * Constructor
	 * @param string $message Error message
	 */
	public function __construct(?string $message = NULL)
	{
		if (empty($message)) {
			$message = dgettext('mfx', "The field '%s' must contain unique values.");
		}
		parent::__construct($message);
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::appliesToField()
	 */
	public function appliesToField(): bool
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field name
	 * @param mixed $value Value to validate
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * 
	 * Note:
	 * The $atIndex parameter is ignored for filters returning true in appliesToField().
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		if (!is_array($value)) {
			return true;
		}

		$value = array_filter($value, function ($item) {
			return ($item !== NULL && $item !== '');
		});
		if (empty($value)) {
			return true;
		}
		$cv = array_count_values($value);
		if (max($cv) != 1) {
			if (!$silent) {
				$this->emitMessage($fieldName);
			}
			return false;
		}
		return true;
	}
}
