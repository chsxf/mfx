<?php

/**
 * Data validation field class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Descriptor of a data validator field
 * @since 1.0
 */
class Field
{
    /**
     * @var string Field's name
     */
    private string $name;

    /**
     * @var FieldType Field's type
     */
    private FieldType $type;

    /**
     * @var boolean If set, this field is required.
     */
    private bool $required;

    /**
     * @var mixed Field's default value
     */
    private mixed $defaultValue;

    /**
     * @var mixed Field's populated value
     */
    private mixed $populatedValue;

    /**
     * @var array Field's filters holder
     */
    private array $filters;

    /**
     * @var bool If set, this field is repeatable.
     */
    private bool $isRepeatable;

    /**
     * @var int Maximum number of iteration for this field. If -1, no limit.
     */
    private int $repeatableUpTo;

    /**
     * @var int Current repeat counter for the generator.
     */
    private int $repeatCounter;

    /**
     * @var bool Read only flag
     */
    private bool $readOnly;

    /**
     * @var bool Disabled flag
     */
    private bool $disabled;

    /**
     * @var boolean If set, the field is populated with the current or default value when generated
     */
    private bool $generateWithValue;

    /**
     * @var array Extra options for field generation
     */
    private array $extras;

    /**
     * Constructor
     * @since 1.0
     * @param String $name Field's name
     * @param FieldType $type Field's type
     * @param mixed $defaultValue Field's default value
     * @param boolean $required If set, this field becomes required
     * @throws DataValidatorException If $name is empty or not a string
     */
    protected function __construct(string $name, FieldType $type, mixed $defaultValue, bool $required)
    {
        if (empty($name) && $name !== '0') {
            throw new DataValidatorException(dgettext('mfx', "Field name cannot be empty."));
        }
        if (!is_string($name)) {
            throw new DataValidatorException(dgettext('mfx', "Expected string as the field name."));
        }

        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->required = !empty($required);

        $this->populatedValue = null;

        $this->filters = array();

        $this->isRepeatable = false;
        $this->repeatableUpTo = -1;
        $this->repeatCounter = 0;

        $this->readOnly = false;
        $this->disabled = false;

        $this->generateWithValue = true;
        $this->extras = array();
    }

    /**
     * Helper function to create fields
     * @since 1.0
     * @param string $name Field's name
     * @param FieldType $type Field's type
     * @param mixed $defaultValue Field's default value (Defaults to NULL)
     * @param boolean $required If set, the field will be required. (Defaults to true)
     * @return Field
     */
    public static function create(string $name, FieldType $type, mixed $defaultValue = null, bool $required = true): Field
    {
        $class = FieldTypeRegistry::getClassForType($type);
        return new $class($name, $type, $defaultValue, $required);
    }

    /**
     * Gets the name of this field
     * @since 1.0
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the type of this field
     * @since 1.0
     * @return FieldType
     */
    public function getType(): FieldType
    {
        return $this->type;
    }

    /**
     * Gets the HTML type of this field
     * @since 1.0
     * @param FieldType $typeOverride Type to use to override original field type. If NULL, no override. (Defaults to NULL)
     * @return string
     */
    public function getHTMLType(?FieldType $typeOverride = null): string
    {
        return ($typeOverride ?? $this->type)->value;
    }

    /**
     * Tells if this field is required or not
     * @since 1.0
     * @return boolean
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Tells if this field as a default value or not
     * @since 1.0
     * @return boolean
     */
    public function hasDefaultValue(): bool
    {
        return ($this->getDefaultValue() !== null);
    }

    /**
     * Get this field's default value
     * @since 1.0
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Sets this field's value
     * @since 1.0
     * @param mixed $value
     */
    public function setValue(mixed $value)
    {
        if ($this->isRepeatable()) {
            if (!is_array($value)) {
                throw new DataValidatorException(sprintf(dgettext('mfx', "Value for repeatable field '%s' must be an array."), $this->getName()));
            }

            // Filtering values with over limit indexes
            if ($this->repeatableUpTo !== null) {
                $keys = array_flip(range(0, $this->repeatableUpTo - 1));
                $value = array_intersect_key($value, $keys);
            }
        }
        $this->populatedValue = $value;
    }

    /**
     * Gets this field's value
     * @since 1.0
     * @param boolean $returnDefaultIfNotSet If set, the function returns the default value if the field has not been populated yet.
     * @return mixed
     */
    public function getValue(bool $returnDefaultIfNotSet = false): mixed
    {
        if ($this->populatedValue !== null) {
            return $this->populatedValue;
        } else {
            return $returnDefaultIfNotSet ? $this->defaultValue : null;
        }
    }

    /**
     * Get a indexed value from this field if repeatable
     * @since 1.0
     * @param int $index Index of the value to retrieve
     * @param bool $returnDefaultIfNotSet If set, the function returns the default value if the field has not been populated yet.
     * @return mixed the indexed value or the field's value if the field is not repeatable.
     */
    public function getIndexedValue(int $index, bool $returnDefaultIfNotSet = false): mixed
    {
        if (!$this->isRepeatable()) {
            return $this->getValue($returnDefaultIfNotSet);
        }

        if ($this->populatedValue === null || (is_array($this->populatedValue) && !array_key_exists($index, $this->populatedValue))) {
            if ($returnDefaultIfNotSet) {
                if (is_array($this->defaultValue)) {
                    if (array_key_exists($index, $this->defaultValue)) {
                        return $this->defaultValue[$index];
                    }
                } else {
                    return $this->defaultValue;
                }
            }
            return null;
        } elseif (!is_array($this->populatedValue)) {
            return $this->populatedValue;
        } else {
            return $this->populatedValue[$index];
        }
    }

    /**
     * Adds a validation filter to this field
     * @since 1.0
     * @param AbstractFilter $filter
     */
    final public function addFilter(AbstractFilter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Removes a validation filter from this field
     * @since 1.0
     * @param AbstractFilter $filter
     */
    final public function removeFilter(AbstractFilter $filter)
    {
        if (!empty($this->filters)) {
            $key = array_search($filter, $this->filters, true);
            if ($key !== false) {
                array_splice($this->filters, $key, 1);
            }
        }
    }

    /**
     * Sets or unsets the field as repeatable
     * @since 1.0
     * @param bool $isRepeatable If set, the field becomes repeatable
     * @param int $upTo Maximum number of iteration. If 0 or negative, no limit is applied. (Defaults to -1)
     */
    final public function setRepeatable(bool $isRepeatable, int $upTo = -1)
    {
        $this->isRepeatable = !empty($isRepeatable);
        if ($upTo <= 0 || !$this->isRepeatable) {
            $this->repeatableUpTo = -1;
        } else {
            $this->repeatableUpTo = $upTo;
        }
    }

    /**
     * Tells if this field is repeatable
     * @since 1.0
     * @return boolean
     */
    final public function isRepeatable(): bool
    {
        return $this->isRepeatable;
    }

    /**
     * Tells the maximum number of iterations for this repeatable field
     * @since 1.0
     * @return int The maximum number of iterations or -1 if no limit.
     */
    final public function repeatableUpTo(): int
    {
        return $this->repeatableUpTo;
    }

    /**
     * Resets this field's repeat counter
     * @since 1.0
     */
    final public function resetRepeatCounter()
    {
        $this->repeatCounter = 0;
    }

    /**
     * Retrieves the maximal defined repeat index for a repeatable field.
     * @since 1.0
     * @return number -1 if no maximal index can be guessed or the actual value
     */
    final public function getMaxRepeatIndex(): int
    {
        if (!$this->isRepeatable()) {
            return -1;
        }

        $value = ($this->populatedValue === null) ? $this->defaultValue : $this->populatedValue;
        if (!is_array($value) || empty($value)) {
            return -1;
        } else {
            return max(array_keys($value));
        }
    }

    /**
     * Sets or unsets the field as read only
     * @since 1.0
     * @param bool $readOnly
     */
    final public function setReadOnly(bool $readOnly)
    {
        $this->readOnly = $readOnly;
    }

    /**
     * Tells if the field is read only
     * @since 1.0
     * @return boolean
     */
    final public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Enables or disables this field
     * @since 1.0
     * @param bool $enabled
     */
    final public function setEnabled(bool $enabled)
    {
        $this->disabled = !$enabled;
    }

    /**
     * Tells if the field is enabled
     * @since 1.0
     * @return boolean
     */
    final public function isEnabled(): bool
    {
        return !$this->disabled;
    }

    /**
     * Enables of disables value population during field generation
     * @since 1.0
     * @param boolean $enabled
     */
    final public function setGenerationWithValue(bool $enabled)
    {
        $this->generateWithValue = $enabled;
    }

    /**
     * Tells if the field should be populated with its value when generated
     * @since 1.0
     * @return boolean
     */
    final public function shouldGenerateWithValue(): bool
    {
        return $this->generateWithValue;
    }

    /**
     * Adds extra option to field
     * @since 1.0
     * @param string $name Option name
     * @param string $value Option value
     */
    final public function addExtra(string $key, string $value)
    {
        $this->extras[$key] = $value;
    }

    /**
     * Adds extra options to field
     * @since 1.0
     * @param array $extras Associative array whose keys are option names and values are option values
     */
    final public function addExtras(array $extras)
    {
        foreach ($extras as $k => $v) {
            $this->addExtra($k, $v);
        }
    }

    /**
     * Tells if the field should be reverted to its default value if it is not populated during validation
     * @since 1.0
     * @return boolean
     */
    public function revertToDefaultIfNotPopulated(): bool
    {
        return false;
    }

    /**
     * Validates the field's value based on the required flag and the provided filters
     * @since 1.0
     * @param boolean $silent If set, no error is triggered (defaults to false)
     * @return boolean
     */
    public function validate(bool $silent = false): bool
    {
        if ($this->isEnabled() == false) {
            return true;
        }

        if ($this->isRepeatable()) {
            $maxIndex = $this->getMaxRepeatIndex();
            if ($this->required && ($maxIndex < 0 || $this->populatedValue === null || !is_array($this->populatedValue))) {
                if (!$silent) {
                    trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
                }
                return false;
            }
            if (!$this->applyFiltersOnField($silent)) {
                return false;
            }
            if ($this->populatedValue !== null && is_array($this->populatedValue)) {
                for ($i = 0; $i <= $maxIndex; $i++) {
                    if (array_key_exists($i, $this->populatedValue)) {
                        if ($this->required && ($this->populatedValue[$i] === null || $this->populatedValue[$i] === '')) {
                            if (!$silent) {
                                trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is required."), $this->getName(), $i));
                            }
                            return false;
                        }
                    }

                    $value = $this->getIndexedValue($i);
                    if (!$this->applyFilterOnValue($value, $i, $silent)) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            if ($this->required && ($this->populatedValue === null || $this->populatedValue === '')) {
                if (!$silent) {
                    trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
                }
                return false;
            }

            if (!$this->applyFiltersOnField($silent)) {
                return false;
            }

            // Filters
            $value = $this->getValue(true);
            return $this->applyFilterOnValue($value, -1, $silent);
        }
    }

    /**
     * Applies applicable field's filters to itself
     * @since 1.0
     * @param boolean $silent If set, no error is triggered (defaults to false)
     * @return boolean true if the field qualifies for all filters, false either.
     *         Note:
     *         This function should be called after having checked to required aspect of the field
     *         as it potentially uses the default value of the field
     */
    protected function applyFiltersOnField(bool $silent = false): bool
    {
        if (!empty($this->filters)) {
            foreach ($this->filters as $f) {
                $fieldValue = $this->getValue(true);
                if ($f->appliesToField() && ($f->isRequired() || $fieldValue !== null) && !$f->validate($this->getName(), $fieldValue, null, $silent)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Applies field's filters to the specified value
     * @since 1.0
     * @param mixed $value Value to validate
     * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
     * @param boolean $silent If set, no error is triggered (defaults to false)
     * @return boolean true if the value qualifies for all filters, false either.
     */
    protected function applyFilterOnValue(mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        $canSkipFilters = (!$this->required && $value === null);
        if (!empty($this->filters)) {
            foreach ($this->filters as $f) {
                if ($f->appliesToField() || ($canSkipFilters && $f->mayBeSkipped($atIndex))) {
                    continue;
                }
                if (!$f->validate($this->getName(), $value, $atIndex, $silent)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Generates the HTML representation of this field
     * @since 1.0
     * @param array $containingGroups Containing groups
     * @param FieldType $typeOverride Type to use to override original field type. If NULL, no override. (Defaults to NULL)
     * @return array
     */
    public function generate(array $containingGroups = array(), ?FieldType $typeOverride = null): array
    {
        if ($this->repeatableUpTo > 0 && $this->repeatCounter + 1 > $this->repeatableUpTo) {
            throw new DataValidatorException(sprintf(dgettext('mfx', "The field '%s' cannot be repeated more than %d times."), $this->getName(), $this->repeatableUpTo));
        }

        $name = $this->getName();
        if (!empty($containingGroups)) {
            $name = sprintf('%s[%s%s', implode('[', $containingGroups), $name, str_pad('', count($containingGroups), ']'));
        }

        return array(
            '@mfx/DataValidator/basic_input.twig',
            array(
                'type' => $this->getHTMLType($typeOverride),
                'name' => $name,
                'required' => $this->isRequired(),
                'readonly' => $this->isReadOnly(),
                'disabled' => !$this->isEnabled(),
                'value' => $this->shouldGenerateWithValue() ? $this->getIndexedValue($this->repeatCounter, true) : null,
                'repeatable' => $this->isRepeatable(),
                'repeat_counter' => $this->repeatCounter++,
                'suffix' => null,
                'extras' => $this->extras
            )
        );
    }
}

$fieldFolder = dirname(__FILE__) . '/Fields';
require_once("{$fieldFolder}/CheckBox.php");
require_once("{$fieldFolder}/DateTime.php");
require_once("{$fieldFolder}/Email.php");
require_once("{$fieldFolder}/File.php");
require_once("{$fieldFolder}/Integer.php");
require_once("{$fieldFolder}/Password.php");
require_once("{$fieldFolder}/TextArea.php");
require_once("{$fieldFolder}/WithOptions.php");
require_once("{$fieldFolder}/Word.php");
