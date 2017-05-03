<?php
/**
 * Data validation Password field type class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

/**
 * Descriptor of a password field type
 */
class Password extends Field
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
		
		$this->setGenerationWithValue(false);
	}
}

FieldType::registerClassForType(new FieldType(FieldType::PASSWORD), Password::class);