<?php

declare(strict_types=1);

/**
 * Data validation Text area field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;

/**
 * Descriptor of a text area field type
 * @since 1.0
 */
class TextArea extends Field
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::generate()
     * @param array $containingGroups
     * @param FieldType $typeOverride
     */
    public function generate(array $containingGroups = array(), ?FieldType $typeOverride = null): array
    {
        $result = parent::generate($containingGroups, $typeOverride);
        $result[0] = '@mfx/DataValidator/textarea.twig';
        return $result;
    }
}

FieldTypeRegistry::registerClassForType(FieldType::TEXTAREA, TextArea::class);
