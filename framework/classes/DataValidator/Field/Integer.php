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
	 * Constructor
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct($name, FieldType $type, $defaultValue, $required) {
		parent::__construct($name, $type, empty($defaultValue) ? 0 : $defaultValue, $required);
		
		switch ($this->getType()->value()) {
			case FieldType::POSITIVE_INTEGER:
				$this->addExtra('min', 1);
				break;
			case FieldType::POSITIVEZERO_INTEGER:
				$this->addExtra('min', 0);
				break;
			case FieldType::NEGATIVE_INTEGER:
				$this->addExtra('max', -1);
				break;
			case FieldType::NEGATIVEZERO_INTEGER:
				$this->addExtra('max', 0);
				break;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::validate()
	 */
	public function validate($silent = false) {
		if (!parent::validate($silent))
			return false;
		
		if ($this->isRepeatable())
		{
			$maxIndex = $this->getMaxRepeatIndex();
			for ($i = 0; $i <= $maxIndex; $i++)
			{
				$val = $this->getIndexedValue($i, true);
				if ($val !== NULL)
				{
					switch ($this->getType()->value())
					{
						case FieldType::INTEGER:
							if (!StringTools::isInteger($val))
							{
								if (empty($silent))
									trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not an integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::POSITIVE_INTEGER:
							if (!StringTools::isPositiveInteger($val))
							{
								if (empty($silent))
									trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly positive integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::POSITIVEZERO_INTEGER:
							if (!StringTools::isPositiveInteger($val, true))
							{
								if (empty($silent))
									trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a positive or zero integer."), $this->getName(), $i));
								return false;
							}
							break;
								
						case FieldType::NEGATIVE_INTEGER:
							if (!StringTools::isNegativeInteger($val))
							{
								if (empty($silent))
									trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly negative integer."), $this->getName(), $i));
								return false;
							}
							break;
					
						case FieldType::NEGATIVEZERO_INTEGER:
							if (!StringTools::isNegativeInteger($val, true))
							{
								if (empty($silent))
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
			if ($val !== NULL)
			{
				switch ($this->getType()->value())
				{
					case FieldType::INTEGER:
						if (!StringTools::isInteger($val))
						{
							if (empty($silent))
								trigger_error(sprintf(dgettext('mfx', "The field '%s' is not an integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::POSITIVE_INTEGER:
						if (!StringTools::isPositiveInteger($val))
						{
							if (empty($silent))
								trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly positive integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::POSITIVEZERO_INTEGER:
						if (!StringTools::isPositiveInteger($val, true))
						{
							if (empty($silent))
								trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a positive or zero integer."), $this->getName()));
							return false;
						}
						break;
						
					case FieldType::NEGATIVE_INTEGER:
						if (!StringTools::isNegativeInteger($val))
						{
							if (empty($silent))
								trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly negative integer."), $this->getName()));
							return false;
						}
						break;
							
					case FieldType::NEGATIVEZERO_INTEGER:
						if (!StringTools::isNegativeInteger($val, true))
						{
							if (empty($silent))
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
	
}

FieldType::registerClassForType(new FieldType(FieldType::INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::POSITIVE_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::POSITIVEZERO_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::NEGATIVE_INTEGER), Integer::class);
FieldType::registerClassForType(new FieldType(FieldType::NEGATIVEZERO_INTEGER), Integer::class);