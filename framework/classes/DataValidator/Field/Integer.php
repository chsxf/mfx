<?php
/**
 * Data validation Integer field type class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;
use CheeseBurgames\MFX\StringTools;

/**
 * Descriptor of an integer field type
 */
class Integer extends Field
{
	/**
	 * (non-PHPdoc)
	 * @see DataValidator_Field::validate()
	 */
	public function validate() {
		if (!parent::validate())
			return false;
		
		if ($this->isRepeatable())
		{
			$maxIndex = $this->getMaxRepeatIndex();
			for ($i = 0; $i <= $maxIndex; $i++)
			{
				$val = $this->getIndexedValue($i, true);
				if (!empty($val))
				{
					switch ($this->getType()->value())
					{
						case FieldType::INTEGER:
							if (!StringTools::isInteger($val))
							{
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not an integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::POSITIVE_INTEGER:
							if (!StringTools::isPositiveInteger($val))
							{
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly positive integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::POSITIVEZERO_INTEGER:
							if (!StringTools::isPositiveInteger($val, true))
							{
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a positive or zero integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::NEGATIVE_INTEGER:
							if (!StringTools::isNegativeInteger($val))
							{
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly negative integer."), $this->getName(), $i));
								return false;
							}
							break;
					
						case FieldType::NEGATIVEZERO_INTEGER:
							if (!StringTools::isNegativeInteger($val, true))
							{
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a negative or zero integer."), $this->getName(), $i));
								return false;
							}
							break;
					}
				}
			}
		}
		else
		{
			$val = $this->getValue(true);
			if (!empty($val))
			{
				switch ($this->getType()->value())
				{
					case FieldType::INTEGER:
						if (!StringTools::isInteger($val))
						{
							trigger_error(sprintf(dgettext('mfx', "The field '%s' is not an integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::POSITIVE_INTEGER:
						if (!StringTools::isPositiveInteger($val))
						{
							trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly positive integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::POSITIVEZERO_INTEGER:
						if (!StringTools::isPositiveInteger($val, true))
						{
							trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a positive or zero integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::NEGATIVE_INTEGER:
						if (!StringTools::isNegativeInteger($val))
						{
							trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly negative integer."), $this->getName()));
							return false;
						}
						break;
							
					case FieldType::NEGATIVEZERO_INTEGER:
						if (!StringTools::isNegativeInteger($val, true))
						{
							trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a negative or zero integer."), $this->getName()));
							return false;
						}
						break;
				}
			}
		}
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::getHTMLType()
	 * @param FieldType $type_override
	 */
	public function getHTMLType(FieldType $type_override = NULL) {
		return parent::getHTMLType(($type_override === NULL) ? new FieldType(FieldType::NUMBER) : $type_override);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$result = parent::generate($containingGroups, $type_override);
		switch ($this->getType()->value()) {
			case FieldType::POSITIVE_INTEGER:
				$result[1]['extras'] = array('min' => 1);
				break;
			case FieldType::POSITIVEZERO_INTEGER:
				$result[1]['extras'] = array('min' => 0);
				break;
			case FieldType::NEGATIVE_INTEGER:
				$result[1]['extras'] = array('max' => -1);
				break;
			case FieldType::NEGATIVEZERO_INTEGER:
				$result[1]['extras'] = array('max' => 0);
				break;
		}
		return $result;
	}
}

FieldType::registerClassForType(new FieldType(FieldType::INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::POSITIVE_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::POSITIVEZERO_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::NEGATIVE_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::NEGATIVEZERO_INTEGER), Integer::class);