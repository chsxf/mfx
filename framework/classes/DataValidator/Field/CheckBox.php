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
class CheckBox extends Field {

	/**
	 * (non-PHPdoc)
	 *
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$result = parent::generate($containingGroups, $type_override);
		if (!empty($result[1]['value']) && $this->shouldGenerateWithValue())
			$result[1]['extras']['checked'] = 'checked';
		$result[1]['value'] = 1;
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Field::revertToDefaultIfNotPopulated()
	 */
	public function revertToDefaultIfNotPopulated() {
		return $this->isEnabled();
	}

}

FieldType::registerClassForType(new FieldType(FieldType::CHECKBOX), CheckBox::class);