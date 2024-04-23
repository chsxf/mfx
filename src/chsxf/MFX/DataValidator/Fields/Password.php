<?php

/**
 * Data validation Password field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;

/**
 * Descriptor of a password field type
 * @since 1.0
 */
class Password extends Field
{

	/**
	 * Constructor
	 * @since 1.0
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct(string $name, FieldType $type, mixed $defaultValue, bool $required)
	{
		parent::__construct($name, $type, $defaultValue, $required);

		$this->setGenerationWithValue(false);
	}
}

FieldTypeRegistry::registerClassForType(FieldType::PASSWORD, Password::class);
