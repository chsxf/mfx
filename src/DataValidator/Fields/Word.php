<?php

declare(strict_types=1);

/**
 * Data validation Word field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;
use chsxf\MFX\DataValidator\Filters\RegExp;

/**
 * Descriptor of a Word field type
 *
 * A "word" character is any letter or digit or the underscore character, that is, any character which can be part of a Perl "word".
 *
 * @since 1.0
 */
class Word extends Field
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

        switch ($type) {
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
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::getHTMLType()
     * @param FieldType $typeOverride
     */
    public function getHTMLType(?FieldType $typeOverride = null): string
    {
        return parent::getHTMLType($typeOverride ?? FieldType::TEXT);
    }
}

FieldTypeRegistry::registerClassForType(FieldType::LOWERCASE_WORD, Word::class);
FieldTypeRegistry::registerClassForType(FieldType::UPPERCASE_WORD, Word::class);
FieldTypeRegistry::registerClassForType(FieldType::WORD, Word::class);
