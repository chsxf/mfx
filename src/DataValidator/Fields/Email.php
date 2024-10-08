<?php

declare(strict_types=1);

/**
 * Data validation Email field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;
use chsxf\MFX\StringTools;

/**
 * Descriptor of an email field type
 * @since 1.0
 */
class Email extends Field
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::validate()
     */
    public function validate(bool $silent = false): bool
    {
        if (!parent::validate($silent)) {
            return false;
        }

        if ($this->isRepeatable()) {
            $maxIndex = $this->getMaxRepeatIndex();
            for ($i = 0; $i <= $maxIndex; $i++) {
                $fieldValue = $this->getIndexedValue($i, true);
                if ($fieldValue !== null && !StringTools::isValidEmailAddress($fieldValue)) {
                    if (!$silent) {
                        trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a valid email address."), $this->getName(), $i));
                    }
                    return false;
                }
            }
        } else {
            $fieldValue = $this->getValue(true);
            if ($fieldValue !== null && !StringTools::isValidEmailAddress($fieldValue)) {
                if (!$silent) {
                    trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a valid email address."), $this->getName()));
                }
                return false;
            }
        }
        return true;
    }
}

FieldTypeRegistry::registerClassForType(FieldType::EMAIL, Email::class);
