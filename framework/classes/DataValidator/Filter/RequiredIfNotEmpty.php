<?php
/**
 * Data validation dependent-requirement filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filter;

use CheeseBurgames\MFX\DataValidator\AbstractOtherFieldFilter;
use CheeseBurgames\MFX\StringTools;
use CheeseBurgames\MFX\DataValidator\Field\CheckBox;

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
	public function __construct($otherFields, $message = NULL)
	{
		parent::__construct($otherFields, $message);
		
		if (empty($message))
		{
			$of = $this->getOtherFields();
			if (count($of) == 1)
				$message = sprintf(dgettext('mfx', "The field '%%s' is required when the field '%s' is provided."), $of[0]->getName());
			else
			{
				$names = array();
				foreach ($of as $f)
					$names[] = $f->getName();
				array_walk($names, function(&$item) {
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
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 */
	public function validate($fieldName, $value, $atIndex = NULL)
	{
		$otherFields = $this->getOtherFields();
		foreach ($otherFields as $f)
		{
			$matchingValue = ($atIndex === NULL) ? $f->getValue() : $f->getIndexedValue($atIndex);
			if ($matchingValue === NULL || $matchingValue === '' || ($f instanceof CheckBox && $matchingValue === 0))
				return true;
		}
		
		if ($value !== NULL && $value !== '')
			return true;
		else
		{
			$this->emitMessage($fieldName);
			return false;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::mayBeSkipped()
	 * 
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 */
	public function mayBeSkipped($atIndex = NULL) {
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