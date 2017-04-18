<?php
/**
 * Data validation Checkbox field type class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

/**
 * Descriptor of a checkbox field type
 */
class CheckBox extends Field
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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$result = parent::generate($containingGroups, $type_override);
		
		$extras = array();
		if (!empty($result[1]['value']))
			$extras['checked'] = 'checked';
		
		$result[1] = array_merge($result[1], array(
				'value' => 1,
				'extras' => $extras
		));
			
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DataValidator_Field::getValue()
	 * 
	 * @param bool $returnDefaultIfNotSet
	 */
	public function getValue($returnDefaultIfNotSet = false) {
		return parent::getValue(true);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DataValidator_Field::getIndexedValue()
	 * 
	 * @param int $index
	 * @param bool $returnDefaultIfNotSet
	 */
	public function getIndexedValue($index, $returnDefaultIfNotSet = false) {
		return parent::getIndexedValue($index, true);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DataValidator_Field::revertToDefaultIfNotPopulated()
	 */
	public function revertToDefaultIfNotPopulated() {
		return $this->isEnabled();
	}
}

FieldType::registerClassForType(new FieldType(FieldType::CHECKBOX), __NAMESPACE__ . '\CheckBox');