<?php
/**
 * Data validation Password field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
namespace chsxf\MFX\DataValidator\Field;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;

/**
 * Descriptor of a password field type
 */
class Password extends Field {

	/**
	 * Constructor
	 *
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct(string $name, FieldType $type, mixed $defaultValue, bool $required) {
		parent::__construct($name, $type, $defaultValue, $required);

		$this->setGenerationWithValue(false);
	}

}

FieldType::registerClassForType(new FieldType(FieldType::PASSWORD), Password::class);