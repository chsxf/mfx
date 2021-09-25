<?php
/**
 * Data validation Text area field type class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Field;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;

/**
 * Descriptor of a text area field type
 */
class TextArea extends Field
{
	/**
	 * (non-PHPdoc)
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$result = parent::generate($containingGroups, $type_override);
		$result[0] = '@mfx/DataValidator/textarea.twig';
		return $result;
	}
}

FieldType::registerClassForType(new FieldType(FieldType::TEXTAREA), TextArea::class);