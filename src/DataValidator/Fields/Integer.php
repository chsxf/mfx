<?php

declare(strict_types=1);

/**
 * Data validation Integer field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;
use chsxf\MFX\StringTools;

/**
 * Descriptor of an integer field type
 * @since 1.0
 */
class Integer extends Field
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

        switch ($this->getType()) {
            case FieldType::POSITIVE_INTEGER:
                $this->addExtra('min', 1);
                break;
            case FieldType::POSITIVEZERO_INTEGER:
                $this->addExtra('min', 0);
                break;
            case FieldType::NEGATIVE_INTEGER:
                $this->addExtra('max', -1);
                break;
            case FieldType::NEGATIVEZERO_INTEGER:
                $this->addExtra('max', 0);
                break;
        }
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

        if ($this->isRepeatable()) {
            $maxIndex = $this->getMaxRepeatIndex();
            for ($i = 0; $i <= $maxIndex; $i++) {
                $val = $this->getIndexedValue($i, true);
                if ($val !== null) {
                    switch ($this->getType()) {
                        case FieldType::INTEGER:
                            if (!StringTools::isInteger($val)) {
                                if (!$silent) {
                                    trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not an integer."), $this->getName(), $i));
                                }
                                return false;
                            }
                            break;

                        case FieldType::POSITIVE_INTEGER:
                            if (!StringTools::isPositiveInteger($val)) {
                                if (!$silent) {
                                    trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly positive integer."), $this->getName(), $i));
                                }
                                return false;
                            }
                            break;

                        case FieldType::POSITIVEZERO_INTEGER:
                            if (!StringTools::isPositiveInteger($val, true)) {
                                if (!$silent) {
                                    trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a positive or zero integer."), $this->getName(), $i));
                                }
                                return false;
                            }
                            break;

                        case FieldType::NEGATIVE_INTEGER:
                            if (!StringTools::isNegativeInteger($val)) {
                                if (!$silent) {
                                    trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a stricly negative integer."), $this->getName(), $i));
                                }
                                return false;
                            }
                            break;

                        case FieldType::NEGATIVEZERO_INTEGER:
                            if (!StringTools::isNegativeInteger($val, true)) {
                                if (!$silent) {
                                    trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is not a negative or zero integer."), $this->getName(), $i));
                                }
                                return false;
                            }
                            break;
                    }
                }
            }
        } else {
            $val = $this->getValue(true);
            if ($val !== null) {
                switch ($this->getType()) {
                    case FieldType::INTEGER:
                        if (!StringTools::isInteger($val)) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' is not an integer."), $this->getName()));
                            }
                            return false;
                        }
                        break;

                    case FieldType::POSITIVE_INTEGER:
                        if (!StringTools::isPositiveInteger($val)) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly positive integer."), $this->getName()));
                            }
                            return false;
                        }
                        break;

                    case FieldType::POSITIVEZERO_INTEGER:
                        if (!StringTools::isPositiveInteger($val, true)) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a positive or zero integer."), $this->getName()));
                            }
                            return false;
                        }
                        break;

                    case FieldType::NEGATIVE_INTEGER:
                        if (!StringTools::isNegativeInteger($val)) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a stricly negative integer."), $this->getName()));
                            }
                            return false;
                        }
                        break;

                    case FieldType::NEGATIVEZERO_INTEGER:
                        if (!StringTools::isNegativeInteger($val, true)) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' is not a negative or zero integer."), $this->getName()));
                            }
                            return false;
                        }
                        break;
                }
            }
        }
        return true;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::getHTMLType()
     * @param FieldType $typeOverride
     */
    public function getHTMLType(?FieldType $typeOverride = null): string
    {
        return parent::getHTMLType(($typeOverride === null) ? FieldType::NUMBER : $typeOverride);
    }
}

FieldTypeRegistry::registerClassForType(FieldType::INTEGER, Integer::class);
FieldTypeRegistry::registerClassForType(FieldType::POSITIVE_INTEGER, Integer::class);
FieldTypeRegistry::registerClassForType(FieldType::POSITIVEZERO_INTEGER, Integer::class);
FieldTypeRegistry::registerClassForType(FieldType::NEGATIVE_INTEGER, Integer::class);
FieldTypeRegistry::registerClassForType(FieldType::NEGATIVEZERO_INTEGER, Integer::class);
