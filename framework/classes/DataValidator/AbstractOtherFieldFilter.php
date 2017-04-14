<?php
/**
 * Data validation abstract field-matching filter class
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator;

/**
 * Description of a filter validating when the field's matched another field's one
 */
abstract class AbstractOtherFieldFilter extends AbstractFilter
{
	/**
	 * @var array References to the matching fields
	 */
	private $_otherFields;

	/**
	 * Constructor
	 * @param DataValidator_Field|array $otherFields One or more references to the matching fields
	 * @param string $message Error message
	 */
	public function __construct($otherFields, $message = NULL)
	{
		parent::__construct($message);
		
		if ($otherFields instanceof DataValidator_Field === false)
		{
			$valid = true;
			if (is_array($otherFields))
			{
				foreach ($otherFields as $v)
				{
					if ($v instanceof DataValidator_Field === false)
					{
						$valid = false;
						break;
					}
				}
			}
			else
				$valid = false;
			if (!$valid)
				throw new DataValidatorException(dgettext('mfx', "References to other fields must be provided as a DataValidator_Field instance or an array containing only instances of this class."));
		}
		
		$this->_otherFields = is_array($otherFields) ? $otherFields : array($otherFields);
	}

	/**
	 * Gets the references to the matching fields
	 * @return array
	 */
	protected function getOtherFields() {
		return $this->_otherFields;
	}
}