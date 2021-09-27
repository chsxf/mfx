<?php
/**
 * Data validation Checkbox field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
namespace chsxf\MFX\DataValidator\Field;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;

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
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL): array {
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
	public function revertToDefaultIfNotPopulated(): bool {
		return $this->isEnabled();
	}

}

FieldType::registerClassForType(new FieldType(FieldType::CHECKBOX), CheckBox::class);