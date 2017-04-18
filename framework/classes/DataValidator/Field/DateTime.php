<?php
namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

class DateTime extends Field {
	
	/**
	 * (non-PHPdoc)
	 * @see Field::validate()
	 */
	public function validate() {
		if (!parent::validate())
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
					trigger_error(sprintf($errorRepeatable, $this->getName(), $i));
					return false;
				}
			}
		}
		else
		{
			if (!preg_match($re, $this->getValue(true)))
			{
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
		$result[1] = array_merge($result[1], array(
				'suffix' => $this->getType()->equals(FieldType::DATE) ? dgettext('mfx', 'yyyy/mm/dd') : dgettext('mfx', 'hh:mm'),
				'extras' => array_merge(empty($result[1]['extras']) ? array() : $result[1]['extras'], array(
						'pattern' => $this->__pattern()
				))
		));
		return $result;
	}
	
	private function __pattern() {
		return $this->getType()->equals(FieldType::DATE) ? '\d{4}/(0\d|1[0-2])/([0-2]\d|3[01])' : '([01]\d|2[0-3]):[0-5]\d';
	}
	
}

FieldType::registerClassForType(new FieldType(FieldType::DATE), __NAMESPACE__ . '\DateTime');
FieldType::registerClassForType(new FieldType(FieldType::TIME), __NAMESPACE__ . '\DateTime');