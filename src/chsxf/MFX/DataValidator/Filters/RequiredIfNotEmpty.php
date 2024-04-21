<?php

/**
 * Data validation dependent-requirement filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractOtherFieldFilter;
use chsxf\MFX\StringTools;
use chsxf\MFX\DataValidator\Fields\CheckBox;
use chsxf\MFX\DataValidator\Field;

/**
 * Description of a filter validating when provided along with other fields.
 */
class RequiredIfNotEmpty extends AbstractOtherFieldFilter
{
	/**
	 * Constructor
	 * @param Field|array $otherFields One or more references to the matching fields
	 * @param string $message Error message
	 */
	public function __construct(Field|array $otherFields, ?string $message = NULL)
	{
		parent::__construct($otherFields, $message);

		if (empty($message)) {
			$of = $this->getOtherFields();
			if (count($of) == 1) {
				$message = sprintf(dgettext('mfx', "The field '%%s' is required when the field '%s' is provided."), $of[0]->getName());
			} else {
				$names = array();
				foreach ($of as $f) {
					$names[] = $f->getName();
				}
				array_walk($names, function (&$item) {
					$item = sprintf("'%s'", $item);
				});
				$names = StringTools::implode(dgettext('mfx', ', '), $names, dgettext('mfx', ' and '));
				$message = sprintf(dgettext('mfx', "The field '%%s' is required when the fields %s are provided."), $names);
			}
			$this->setMessage($message);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		$otherFields = $this->getOtherFields();
		foreach ($otherFields as $f) {
			$matchingValue = ($atIndex < 0) ? $f->getValue() : $f->getIndexedValue($atIndex);
			if ($matchingValue === null || $matchingValue === '' || ($f instanceof CheckBox && $matchingValue === 0)) {
				return true;
			}
		}

		if ($value !== null && $value !== '') {
			return true;
		} else {
			if (!$silent) {
				$this->emitMessage($fieldName);
			}
			return false;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::mayBeSkipped()
	 * 
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 */
	public function mayBeSkipped(int $atIndex = -1): bool
	{
		$otherFields = $this->getOtherFields();
		foreach ($otherFields as $f) {
			$v = ($atIndex === NULL) ? $f->getValue() : $f->getIndexedValue($atIndex);
			if ($v !== null) {
				return false;
			}
		}
		return true;
	}
}
