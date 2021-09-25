<?php
/**
 * Data validation Word field type class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */	

namespace chsxf\MFX\DataValidator\Field;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\Filter\RegExp;

/**
 * Descriptor of a Word field type
 * 
 * A "word" character is any letter or digit or the underscore character, that is, any character which can be part of a Perl "word".
 */
class Word extends Field
{
	/**
	 * Constructor
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct($name, FieldType $type, $defaultValue, $required) {
		parent::__construct($name, $type, $defaultValue, $required);
		
		switch ($type->value())
		{
			case FieldType::LOWERCASE_WORD:
				$this->addFilter(RegExp::lowerCaseWord());
				break;
			case FieldType::UPPERCASE_WORD:
				$this->addFilter(RegExp::upperCaseWord());
				break;
			case FieldType::WORD:
				$this->addFilter(RegExp::word());
				break;
		} 
		
		$this->_lastLengthFilter = NULL;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::getHTMLType()
	 * @param FieldType $type_override
	 */
	public function getHTMLType(FieldType $type_override = NULL) {
		return parent::getHTMLType(($type_override === NULL) ? new FieldType(FieldType::TEXT) : $type_override);
	}
}

FieldType::registerClassForType(new FieldType(FieldType::LOWERCASE_WORD), Word::class);
FieldType::registerClassForType(new FieldType(FieldType::UPPERCASE_WORD), Word::class);
FieldType::registerClassForType(new FieldType(FieldType::WORD), Word::class);
