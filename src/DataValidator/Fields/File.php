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
 * @since 1.0
 */
class File extends Field
{
    private const REQUIRED_KEYS = array(
        'name',
        'type',
        'tmp_name',
        'error',
        'size'
    );

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

        $error = dgettext('mfx', "The field '%s' does not contain a valid file.");
        $errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid file.");

        if ($this->isRepeatable()) {
            $maxIndex = $this->getMaxRepeatIndex();
            for ($i = 0; $i <= $maxIndex; $i++) {
                $value = $this->getIndexedValue($i, true);
                if (($this->isRequired() || !empty($value)) && !$this->validateFileData($value)) {
                    if (!$silent) {
                        trigger_error(sprintf($errorRepeatable, $this->getName(), $i));
                    }
                    return false;
                }
            }
        } else {
            $value = $this->getValue(true);
            if (($this->isRequired() || !empty($value)) && !$this->validateFileData($value)) {
                if (!$silent) {
                    trigger_error(sprintf($error, $this->getName()));
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a file data entry
     *
     * @param mixed $data
     */
    private function validateFileData(mixed $data): bool
    {
        $isValid = false;
        if (is_array($data)) {
            $intersect = array_intersect(self::REQUIRED_KEYS, array_keys($data));
            $isValid = (count($intersect) == count(self::REQUIRED_KEYS));

            if ($isValid) {
                $isValid = ($data['error'] == 0);
            }
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     * @ignore
     * @see \chsxf\MFX\DataValidator\Field::setValue()
     */
    public function setValue(mixed $value)
    {
        if ($this->isRepeatable()) {
            if (is_array($value)) {
                $value = array_filter($value, function ($item) {
                    return isset($item['error']) && $item['error'] != UPLOAD_ERR_NO_FILE;
                });
            }
            parent::setValue($value);
        } else {
            if (isset($value['error']) && $value['error'] != UPLOAD_ERR_NO_FILE) {
                parent::setValue($value);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::revertToDefaultIfNotPopulated()
     */
    public function revertToDefaultIfNotPopulated(): bool
    {
        return true;
    }
}

FieldTypeRegistry::registerClassForType(FieldType::FILE, File::class);
