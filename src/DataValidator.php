<?php

/**
 * Data validation helper
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\MFX\DataValidator\DataValidatorException;
use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;

/**
 * Data validator class
 *
 * Instances of this class are used as a entry point for data validation procedures.
 *
 * @since 1.0
 */
final class DataValidator implements \ArrayAccess
{
    /**
     * @var array Fields container
     */
    private array $fields;
    /**
     * @var array Generation groups
     */
    private array $generationGroups;

    /**
     * Constructor
     * @since 1.0
     * @param array $fields Pre-allocated data validation fields
     */
    public function __construct(array $fields = null)
    {
        $this->fields = array();

        if (!empty($fields)) {
            foreach ($fields as $f) {
                $this->addField($f);
            }
        }

        $this->generationGroups = array();
    }

    /**
     * Adds a field to the data validator
     * @since 1.0
     * @param \chsxf\MFX\DataValidator\Field $field
     * @throws \chsxf\MFX\DataValidator\DataValidatorException If a field with the same name already exists
     * @return \chsxf\MFX\DataValidator\Field The added field
     */
    public function addField(Field $field): Field
    {
        if (array_key_exists($field->getName(), $this->fields)) {
            throw new DataValidatorException(dgettext('mfx', "Duplicate field name '{$field->getName()}'."));
        }
        $this->fields[$field->getName()] = $field;
        return $field;
    }

    /**
     * Creates and add a field to the data validator
     * @since 1.0
     * @param string $name Field's name
     * @param \chsxf\MFX\DataValidator\FieldType $type Field's type
     * @param mixed $defaultValue Field's default value (Defaults to NULL)
     * @param boolean $required If set, the field will be required. (Defaults to true)
     * @param array $extras Extra options
     * @return \chsxf\MFX\DataValidator\Field
     *
     * @see Field::addField()
     */
    public function createField(string $name, FieldType $type, mixed $defaultValue = null, bool $required = true, array $extras = array()): Field
    {
        $f = $this->addField(Field::create($name, $type, $defaultValue, $required));
        $f->addExtras($extras);
        return $f;
    }

    /**
     * Gets the value of a field
     * @since 1.0
     * @param string $name Field name
     * @param boolean $returnDefaultIfNotSet If set, the default value of the field is returned is none has been provided (Defaults to false)
     * @param boolean $makeEmptyStringsNull If set, all empty string values will be set to NULL. (Defaults to false)
     * @throws \chsxf\MFX\DataValidator\DataValidatorException If no field exists with this name
     */
    public function getFieldValue(string $name, bool $returnDefaultIfNotSet = false, bool $makeEmptyStringsNull = false): mixed
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new DataValidatorException(dgettext('mfx', "Unknown '{$name}' field."));
        }
        $val = $this->fields[$name]->getValue($returnDefaultIfNotSet);
        if ($makeEmptyStringsNull && is_string($val) && empty($val)) {
            $val = null;
        }
        return $val;
    }

    /**
     * Gets the value of a field
     * @since 1.0
     * @param string $name Field name
     * @param int $index Index of the value to retrieve
     * @param boolean $returnDefaultIfNotSet If set, the default value of the field is returned is none has been provided (Defaults to false)
     * @param boolean $makeEmptyStringsNull If set, all empty string values will be set to NULL. (Defaults to false)
     * @throws \chsxf\MFX\DataValidator\DataValidatorException If no field exists with this name
     */
    public function getIndexedFieldValue(string $name, int $index, bool $returnDefaultIfNotSet = false, bool $makeEmptyStringsNull = false): mixed
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new DataValidatorException(dgettext('mfx', "Unknown '{$name}' field."));
        }
        $val = $this->fields[$name]->getIndexedValue($index, $returnDefaultIfNotSet);
        if ($makeEmptyStringsNull && is_string($val) && empty($val)) {
            $val = null;
        }
        return $val;
    }

    /**
     * Gets values for all fields
     * @since 1.0
     * @param string $prefix Prefix to use for all field name in the resulting array (Defaults to no prefix)
     * @param array $excludes List of fields to exclude from the resulting array (Defaults to an empty array)
     * @param string $returnDefaultIfNotSet If set, the default value of the field is returned if none has been provided (Defaults to false)
     * @param boolean $makeEmptyStringsNull If set, all empty string values will be set to NULL. (Defaults to false)
     * @return array
     */
    public function getFieldValues(string $prefix = '', array $excludes = array(), bool $returnDefaultIfNotSet = false, bool $makeEmptyStringsNull = false): array
    {
        if (!is_array($excludes)) {
            $excludes = array();
        }
        $values = array();
        foreach ($this->fields as $n => $f) {
            if (!in_array($n, $excludes)) {
                $v = $f->getValue($returnDefaultIfNotSet);
                if ($makeEmptyStringsNull && is_string($v) && empty($v)) {
                    $v = null;
                }
                $values[$prefix . $n] = $v;
            }
        }
        return $values;
    }

    /**
     * Gets indexed values for all fields
     * @since 1.0
     * @param int $index Index of the values in fields
     * @param string $name Field name
     * @param string $prefix Prefix to use for all field name in the resulting array (Defaults to no prefix)
     * @param array $excludes List of fields to exclude from the resulting array (Defaults to an empty array)
     * @param string $returnDefaultIfNotSet If set, the default value of the field is returned if none has been provided (Defaults to false)
     * @param boolean $makeEmptyStringsNull If set, all empty string values will be set to NULL. (Defaults to false)
     * @return array
     */
    public function getIndexedFieldValues(int $index, string $prefix = '', array $excludes = array(), bool $returnDefaultIfNotSet = false, bool $makeEmptyStringsNull = false): array
    {
        if (!is_array($excludes)) {
            $excludes = array();
        }
        $values = array();
        foreach ($this->fields as $n => $f) {
            if (!in_array($n, $excludes)) {
                $v = $f->getIndexedValue($index, $returnDefaultIfNotSet);
                if ($makeEmptyStringsNull && is_string($v) && empty($v)) {
                    $v = null;
                }
                $values[$prefix . $n] = $v;
            }
        }
        return $values;
    }

    /**
     * Resets the repeat counters for all fields
     * @since 1.0
     */
    public function resetRepeatCounters()
    {
        foreach ($this->fields as $f) {
            $f->resetRepeatCounter();
        }
    }

    /**
     * Validates data based on field descriptors
     * @since 1.0
     * @param array|\Traversable $data Data to validate
     * @param boolean $silent If set, no error is triggered (defaults to false)
     * @return boolean true if data is valid, false either
     */
    public function validate(array|\Traversable $data, bool $silent = false): bool
    {
        // Populate data
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $this->fields)) {
                $this->fields[$k]->setValue($v);
            }
        }

        // Revert unpopulated applying fields to default value
        foreach ($this->fields as $k => $v) {
            if ($v->revertToDefaultIfNotPopulated() && !array_key_exists($k, $data)) {
                $v->setValue($v->isRepeatable() ? array() : null);
            }
        }

        // Validation
        foreach ($this->fields as $f) {
            if (!$f->validate($silent)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generates the HTML representation of a field
     * @since 1.0
     * @param string $name Field's name to generate
     * @param \chsxf\MFX\DataValidator\FieldType $typeOverride Type to use to override original field type. If NULL, no override. (Defaults to NULL)
     * @throws \chsxf\MFX\DataValidator\DataValidatorException If no field exists with this name
     */
    public function generate(string $name, FieldType $typeOverride = null)
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new DataValidatorException(dgettext('mfx', "Unknown '{$name}' field."));
        }
        return $this->fields[$name]->generate($this->generationGroups, $typeOverride);
    }

    /**
     * Pushes a generation group name
     * @since 1.0
     * @param string $name
     */
    public function pushGenerationGroup(string $name)
    {
        if (!empty($name) && is_string($name)) {
            $this->generationGroups[] = $name;
        }
    }

    /**
     * Pops a generation group name
     * @since 1.0
     */
    public function popGenerationGroup()
    {
        if (!empty($this->generationGroups)) {
            array_pop($this->generationGroups);
        }
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \ArrayAccess::offsetExists()
     * @param mixed $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return (is_string($offset) && array_key_exists($offset, $this->fields));
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \ArrayAccess::offsetGet()
     * @param mixed $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getFieldValue($offset);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \ArrayAccess::offsetSet()
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->offsetUnset($offset);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \ArrayAccess::offsetUnset()
     * @param mixed $offset
     * @throws \chsxf\MFX\DataValidator\DataValidatorException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new DataValidatorException(dgettext('mfx', "Field values cannot be removed."));
    }
}
