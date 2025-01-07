<?php

declare(strict_types=1);

/**
 * Data validation field with multiple options type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Fields;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\FieldTypeRegistry;

/**
 * Descriptor of a field type with multiple options (such as 'select' or 'radio' types)
 * @since 1.0
 */
class WithOptions extends Field
{
    /**
     * @var array Options holder
     */
    private array $_options = array();

    /**
     * Add an option to the list
     * @since 1.0
     * @param string $label Option label
     * @param string $value Option value. Equals to label if NULL. (Defaults to NULL)
     * @param string $group Option group. If set, the option will be added to the corresponding group. (Defaults to NULL)
     */
    public function addOption(string $label, ?string $value = null, ?string $group = null)
    {
        if ($value === null) {
            $value = $label;
        }
        $option = array('value' => $value, 'label' => $label, 'group' => $group);
        $this->_options[] = $option;
    }

    /**
     * Add options to the list
     * @since 1.0
     * @param array $options Options array. Items may be mixed scalar values and arrays. If an item is an array, it should have keys 'value' and 'label'.
     * @param bool $useAsKeyValueStore If set, items for the $options array that are not arrays themselves will be considered as key/value pairs. If not set, item value will be used for label and value. (Defaults to false)
     * @param string $group Options group. If set, the options will be added to the corresponding group. (Defaults to NULL)
     */
    public function addOptions(array $options, bool $useAsKeyValueStore = false, ?string $group = null)
    {
        array_walk($options, function (&$item, $key, $userData) {
            if (!is_array($item)) {
                $item = array('value' => ($userData[0] || is_string($key)) ? $key : $item, 'label' => $item);
            }
            $item['group'] = $userData[1];
        }, array($useAsKeyValueStore, $group));

        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::validate()
     */
    public function validate(bool $silent = false): bool
    {
        if ($this->isEnabled() == false) {
            return true;
        }

        $value = $this->getValue(!$this->isRequired());

        // Checks value against required status
        if ($this->isRequired() && empty($value)) {
            if (!$silent) {
                trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
            }
            return false;
        }

        if ($this->isRepeatable()) {
            // Checks value against required status
            if (!is_array($value)) {
                if (!$silent) {
                    trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
                }
                return false;
            }

            // Validates through filters
            if (!$this->applyFiltersOnField($silent)) {
                return false;
            }

            $maxIndex = $this->getMaxRepeatIndex();
            for ($i = 0; $i <= $maxIndex; $i++) {
                $val = $this->getIndexedValue($i);

                // Checks value against required status
                if ($this->isRequired() && empty($val)) {
                    if (!$silent) {
                        trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is required."), $this->getName(), $i));
                    }
                    return false;
                }

                if (!is_array($val)) {
                    $val = array($val);
                }

                // Checks value against options and applies filters
                foreach ($val as $v) {
                    if (!$this->isValidOption($v)) {
                        if (!$silent) {
                            trigger_error(sprintf(dgettext('mfx', "'%s' is not a valid value for the '%s' field at index %d."), $v, $this->getName(), $i));
                        }
                        return false;
                    }

                    if (!$this->applyFilterOnValue($v, $i, $silent)) {
                        return false;
                    }
                }
            }
        } else {
            // Validates through filters
            if (!$this->applyFiltersOnField($silent)) {
                return false;
            }

            if (!is_array($value)) {
                $value = array($value);
            }

            // Checks value against options and applies filters
            foreach ($value as $v) {
                if (!$this->isValidOption($v)) {
                    if (!$silent) {
                        trigger_error(sprintf(dgettext('mfx', "'%s' is not a valid value for the '%s' field."), $v, $this->getName()));
                    }
                    return false;
                }

                if (!$this->applyFilterOnValue($v, -1, $silent)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Tells if the specified value is a valid option, based on the list.
     * @since 1.0
     * @param mixed $value
     * @return boolean true if the value is a valid option, false either.
     */
    private function isValidOption(mixed $value): bool
    {
        foreach ($this->_options as $opt) {
            if ($opt['value'] == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::getHTMLType()
     * @param FieldType $typeOverride
     */
    public function getHTMLType(?FieldType $typeOverride = null): string
    {
        return ($this->getType() === FieldType::MULTI_SELECT) ? 'select' : parent::getHTMLType($typeOverride);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see Field::generate()
     * @param array $containingGroups
     * @param FieldType $typeOverride
     */
    public function generate(array $containingGroups = array(), ?FieldType $typeOverride = null): array
    {
        $template = ($this->getType() === FieldType::RADIO) ? '@mfx/DataValidator/radio.twig' : '@mfx/DataValidator/select.twig';

        $hasOptionGroup = false;
        foreach ($this->_options as $opt) {
            if (!empty($opt['group'])) {
                $hasOptionGroup = true;
            }
        }

        if ($hasOptionGroup) {
            $unsortedOptions = array() + $this->_options;
            usort($unsortedOptions, function ($_itemA, $_itemB) {
                return strcasecmp($_itemA['group'], $_itemB['group']);
            });

            $optionsToGenerate = array();
            $lastGroup = null;
            foreach ($unsortedOptions as $opt) {
                if ($opt['group'] !== $lastGroup) {
                    $lastGroup = $opt['group'];
                    $optionsToGenerate[] = array('optgroup' => $lastGroup);
                }
                $optionsToGenerate[] = $opt;
            }
        } else {
            $optionsToGenerate = $this->_options;
        }

        $result = parent::generate($containingGroups, $typeOverride);
        if ($typeOverride === null || $typeOverride !== FieldType::HIDDEN) {
            $result[0] = $template;
            $result[1] = array_merge($result[1], array(
                'name' => ($this->getType() === FieldType::MULTI_SELECT) ? sprintf('%s[]', $result[1]['name']) : $result[1]['name'],
                'multiple' => ($this->getType() === FieldType::MULTI_SELECT),
                'options' => $optionsToGenerate
            ));
        }
        return $result;
    }
}

FieldTypeRegistry::registerClassForType(FieldType::SELECT, WithOptions::class);
FieldTypeRegistry::registerClassForType(FieldType::MULTI_SELECT, WithOptions::class);
FieldTypeRegistry::registerClassForType(FieldType::RADIO, WithOptions::class);
