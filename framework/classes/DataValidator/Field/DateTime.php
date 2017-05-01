<?php
namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

class DateTime extends Field {

	/**
	 * Constructor
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct($name, FieldType $type, $defaultValue, $required) {
		parent::__construct($name, $type, empty($defaultValue) ? 0 : $defaultValue, $required);
		
		$this->addExtra('pattern', $this->getType()->equals(FieldType::DATE) ? '\d{4}/(0\d|1[0-2])/([0-2]\d|3[01])' : '([01]\d|2[0-3]):[0-5]\d');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::validate()
	 */
	public function validate($silent = false) {
		if (!parent::validate($silent))
			return false;
		
		$re = sprintf('#^%s$#', $this->__pattern());
		switch ($this->getType()->value()) {
			case FieldType::DATE:
				$error = dgettext('mfx', "The field '%s' does not contain a valid date.");
				$errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid date.");
				break;
			case FieldType::TIME:
				$error = dgettext('mfx', "The field '%s' does not contain a valid time.");
				$errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid time.");
				break;
		}
		
		if ($this->isRepeatable())
		{
			$maxIndex = $this->getMaxRepeatIndex();
			for ($i = 0; $i <= $maxIndex; $i++)
			{
				if (!preg_match($re, $this->getIndexedValue($i, true)))
				{
					if (empty($silent))
						trigger_error(sprintf($errorRepeatable, $this->getName(), $i));
					return false;
				}
			}
		}
		else
		{
			if (!preg_match($re, $this->getValue(true)))
			{
				if (empty($silent))
					trigger_error(sprintf($error, $this->getName()));
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$result = parent::generate($containingGroups, $type_override);
		$result[1]['suffix'] = $this->getType()->equals(FieldType::DATE) ? dgettext('mfx', 'yyyy/mm/dd') : dgettext('mfx', 'hh:mm');
		return $result;
	}
	
}

FieldType::registerClassForType(new FieldType(FieldType::DATE), DateTime::class);
FieldType::registerClassForType(new FieldType(FieldType::TIME), DateTime::class);