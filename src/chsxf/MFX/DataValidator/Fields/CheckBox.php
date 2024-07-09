<?php

/**
 * Data validation Checkbox field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;

/**
 * Descriptor of a checkbox field type
 *
 * @since 1.0
 */
class CheckBox extends Field
{
    /**
     * (non-PHPdoc)
     *
     * @ignore
     *
     * @see Field::generate()
     * @param array $containingGroups
     * @param FieldType $type_override
     */
    public function generate(array $containingGroups = array(), ?FieldType $type_override = null): array
    {
        $result = parent::generate($containingGroups, $type_override);
        if (!empty($result[1]['value']) && $this->shouldGenerateWithValue()) {
            $result[1]['extras']['checked'] = 'checked';
        }
        $result[1]['value'] = 1;
        return $result;
    }

    /**
     * (non-PHPdoc)
     *
     * @ignore
     *
     * @see Field::revertToDefaultIfNotPopulated()
     */
    public function revertToDefaultIfNotPopulated(): bool
    {
        return $this->isEnabled();
    }
}

FieldTypeRegistry::registerClassForType(FieldType::CHECKBOX, CheckBox::class);
