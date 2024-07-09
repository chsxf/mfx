<?php

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;

/**
 * @since 1.0
 */
class DateTime extends Field
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
        parent::__construct($name, $type, empty($defaultValue) ? 0 : $defaultValue, $required);
    }

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

        $re = sprintf('#^%s$#', self::regexPattern($this->getType()));
        switch ($this->getType()) {
            case FieldType::DATE:
                $error = dgettext('mfx', "The field '%s' does not contain a valid date.");
                $errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid date.");
                break;
            case FieldType::TIME:
                $error = dgettext('mfx', "The field '%s' does not contain a valid time.");
                $errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid time.");
                break;
        }

        if ($this->isRepeatable()) {
            $maxIndex = $this->getMaxRepeatIndex();
            for ($i = 0; $i <= $maxIndex; $i++) {
                if (!preg_match($re, $this->getIndexedValue($i, true))) {
                    if (!$silent) {
                        trigger_error(sprintf($errorRepeatable, $this->getName(), $i));
                    }
                    return false;
                }
            }
        } else {
            if (!preg_match($re, $this->getValue(true))) {
                if (!$silent) {
                    trigger_error(sprintf($error, $this->getName()));
                }
                return false;
            }
        }

        return true;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::generate()
     * @param array $containingGroups
     * @param FieldType $type_override
     */
    public function generate(array $containingGroups = array(), ?FieldType $type_override = null): array
    {
        $result = parent::generate($containingGroups, $type_override);
        $result[1]['suffix'] = self::humanlyReadablePattern($this->getType());
        return $result;
    }

    /**
     * Gets the pattern to use with the date() function
     * @since 1.0
     * @param FieldType $type Type of the field
     * @return string
     */
    public static function dateFunctionPattern(FieldType $type): string
    {
        return ($type === FieldType::DATE) ? 'Y-m-d' : 'H:i';
    }

    /**
     * Gets the pattern as humanly readable
     * @since 1.0
     * @param FieldType $type Type of the field
     * @return string
     */
    public static function humanlyReadablePattern(FieldType $type): string
    {
        return ($type === FieldType::DATE) ? dgettext('mfx', 'mm/dd/yyyy') : dgettext('mfx', 'hh:mm');
    }

    /**
     * Gets the pattern as a regular expression
     * @since 1.0
     * @param FieldType $type Type of the field
     * @param boolean $withBackReferences If set, the function should return a regular expression pattern containing name back references
     * @return string
     */
    public static function regexPattern(FieldType $type, bool $withBackReferences = false): string
    {
        if (empty($withBackReferences)) {
            return ($type === FieldType::DATE) ? '\d{4}-(0\d|1[0-2])-([0-2]\d|3[01])' : '([01]\d|2[0-3]):[0-5]\d';
        } else {
            return ($type === FieldType::DATE) ? '(?<year>\d{4})-(?<month>0\d|1[0-2])-(?<day>[0-2]\d|3[01])' : '(?<hour>[01]\d|2[0-3]):(?<minute>[0-5]\d)';
        }
    }
}

FieldTypeRegistry::registerClassForType(FieldType::DATE, DateTime::class);
FieldTypeRegistry::registerClassForType(FieldType::TIME, DateTime::class);
