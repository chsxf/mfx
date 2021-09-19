<?php
/**
 * Data validation field-matching filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractOtherFieldFilter;
use chsxf\MFX\StringTools;
use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating when the field's matched another field's one
 */
class Match extends AbstractOtherFieldFilter
{
	/**
	 * Constructor
	 * @param Field|array $otherFields One or more references to the matching fields
	 * @param string $message Error message
	 */
	public function __construct($otherFields, $message = NULL)
	{
		parent::__construct($otherFields, $message);
		
		if (empty($message))
		{
			$of = $this->getOtherFields();
			if (count($of) == 1)
				$message = sprintf(dgettext('mfx', "The field '%%s' must match the value of the field '%s'."), $of[0]->getName());
			else
			{
				$names = array();
				foreach ($of as $f)
					$names[] = $f->getName();
				array_walk($names, function(&$item) {
					$item = sprintf("'%s'", $item);
				});
				$names = StringTools::implode(dgettext('mfx', ', '), $names, dgettext('mfx', ' and '));
				$message = sprintf(dgettext('mfx', "The field '%%s' must match the value of the fields %s."), $names);
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
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false)
	{
		$otherFields = $this->getOtherFields();
		foreach ($otherFields as $f)
		{
			$matchingValue = ($atIndex === NULL) ? $f->getValue() : $f->getIndexedValue($atIndex);
			if ($value != $matchingValue)
			{
				if (empty($silent))
					$this->emitMessage($fieldName);
				return false;
			}
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::mayBeSkipped()
	 * 
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 */
	public function mayBeSkipped($atIndex = NULL)
	{
		$otherFields = $this->getOtherFields();
		foreach ($otherFields as $f)
		{
			$v = ($atIndex === NULL) ? $f->getValue() : $f->getIndexedValue($atIndex);
			if ($v !== NULL)
				return false;
		}
		return true;
	}
}