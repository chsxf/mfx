<?php

/**
 * Data validation field class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Descriptor of a data validator field
 */
class Field
{

	/**
	 * @var string Field's name
	 */
	private string $_name;

	/**
	 * @var FieldType Field's type
	 */
	private FieldType $_type;

	/**
	 * @var boolean If set, this field is required.
	 */
	private bool $_required;

	/**
	 * @var mixed Field's default value
	 */
	private mixed $_defaultValue;

	/**
	 * @var mixed Field's populated value
	 */
	private mixed $_populatedValue;

	/**
	 * @var array Field's filters holder
	 */
	private array $_filters;

	/**
	 * @var bool If set, this field is repeatable.
	 */
	private bool $_isRepeatable;

	/**
	 * @var int Maximum number of iteration for this field. If -1, no limit.
	 */
	private int $_repeatableUpTo;

	/**
	 * @var int Current repeat counter for the generator.
	 */
	private int $_repeatCounter;

	/**
	 * @var bool Read only flag
	 */
	private bool $_readOnly;

	/**
	 * @var bool Disabled flag
	 */
	private bool $_disabled;

	/**
	 * @var boolean If set, the field is populated with the current or default value when generated
	 */
	private bool $_generateWithValue;

	/**
	 * @var array Extra options for field generation
	 */
	private array $_extras;

	/**
	 * Constructor
	 *
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

		$this->_name = $name;
		$this->_type = $type;
		$this->_defaultValue = $defaultValue;
		$this->_required = !empty($required);

		$this->_populatedValue = NULL;

		$this->_filters = array();

		$this->_isRepeatable = false;
		$this->_repeatableUpTo = -1;
		$this->_repeatCounter = 0;

		$this->_readOnly = false;
		$this->_disabled = false;

		$this->_generateWithValue = true;
		$this->_extras = array();
	}

	/**
	 * Helper function to create fields
	 *
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value (Defaults to NULL)
	 * @param boolean $required If set, the field will be required. (Defaults to true)
	 * @return Field
	 */
	public static function create(string $name, FieldType $type, mixed $defaultValue = NULL, bool $required = true): Field
	{
		$class = FieldTypeRegistry::getClassForType($type);
		return new $class($name, $type, $defaultValue, $required);
	}

	/**
	 * Gets the name of this field
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * Gets the type of this field
	 *
	 * @return FieldType
	 */
	public function getType(): FieldType
	{
		return $this->_type;
	}

	/**
	 * Gets the HTML type of this field
	 *
	 * @param FieldType $type_override Type to use to override original field type. If NULL, no override. (Defaults to NULL)
	 * @return string
	 */
	public function getHTMLType(?FieldType $type_override = NULL): string
	{
		return ($type_override ?? $this->_type)->value;
	}

	/**
	 * Tells if this field is required or not
	 *
	 * @return boolean
	 */
	public function isRequired(): bool
	{
		return $this->_required;
	}

	/**
	 * Tells if this field as a default value or not
	 *
	 * @return boolean
	 */
	public function hasDefaultValue(): bool
	{
		return ($this->getDefaultValue() !== NULL);
	}

	/**
	 * Get this field's default value
	 *
	 * @return mixed
	 */
	public function getDefaultValue(): mixed
	{
		return $this->_defaultValue;
	}

	/**
	 * Sets this field's value
	 *
	 * @param mixed $value
	 */
	public function setValue(mixed $value)
	{
		if ($this->isRepeatable()) {
			if (!is_array($value)) {
				throw new DataValidatorException(sprintf(dgettext('mfx', "Value for repeatable field '%s' must be an array."), $this->getName()));
			}

			// Filtering values with over limit indexes
			if ($this->_repeatableUpTo !== NULL) {
				$keys = array_flip(range(0, $this->_repeatableUpTo - 1));
				$value = array_intersect_key($value, $keys);
			}
		}
		$this->_populatedValue = $value;
	}

	/**
	 * Gets this field's value
	 *
	 * @param boolean $returnDefaultIfNotSet If set, the function returns the default value if the field has not been populated yet.
	 * @return mixed
	 */
	public function getValue(bool $returnDefaultIfNotSet = false): mixed
	{
		if ($this->_populatedValue !== NULL) {
			return $this->_populatedValue;
		} else {
			return $returnDefaultIfNotSet ? $this->_defaultValue : NULL;
		}
	}

	/**
	 * Get a indexed value from this field if repeatable
	 *
	 * @param int $index Index of the value to retrieve
	 * @param bool $returnDefaultIfNotSet If set, the function returns the default value if the field has not been populated yet.
	 * @return mixed the indexed value or the field's value if the field is not repeatable.
	 */
	public function getIndexedValue(int $index, bool $returnDefaultIfNotSet = false): mixed
	{
		if (!$this->isRepeatable()) {
			return $this->getValue($returnDefaultIfNotSet);
		}

		if ($this->_populatedValue === NULL || (is_array($this->_populatedValue) && !array_key_exists($index, $this->_populatedValue))) {
			if ($returnDefaultIfNotSet) {
				if (is_array($this->_defaultValue)) {
					if (array_key_exists($index, $this->_defaultValue)) {
						return $this->_defaultValue[$index];
					}
				} else {
					return $this->_defaultValue;
				}
			}
			return NULL;
		} else if (!is_array($this->_populatedValue)) {
			return $this->_populatedValue;
		} else {
			return $this->_populatedValue[$index];
		}
	}

	/**
	 * Adds a validation filter to this field
	 *
	 * @param AbstractFilter $filter
	 */
	public final function addFilter(AbstractFilter $filter)
	{
		$this->_filters[] = $filter;
	}

	/**
	 * Removes a validation filter from this field
	 *
	 * @param AbstractFilter $filter
	 */
	public final function removeFilter(AbstractFilter $filter)
	{
		if (!empty($this->_filters)) {
			$key = array_search($filter, $this->_filters, true);
			if ($key !== false) {
				array_splice($this->_filters, $key, 1);
			}
		}
	}

	/**
	 * Sets or unsets the field as repeatable
	 *
	 * @param bool $isRepeatable If set, the field becomes repeatable
	 * @param int $upTo Maximum number of iteration. If 0 or negative, no limit is applied. (Defaults to -1)
	 */
	public final function setRepeatable(bool $isRepeatable, int $upTo = -1)
	{
		$this->_isRepeatable = !empty($isRepeatable);
		if ($upTo <= 0 || !$this->_isRepeatable) {
			$this->_repeatableUpTo = -1;
		} else {
			$this->_repeatableUpTo = $upTo;
		}
	}

	/**
	 * Tells if this field is repeatable
	 *
	 * @return boolean
	 */
	public final function isRepeatable(): bool
	{
		return $this->_isRepeatable;
	}

	/**
	 * Tells the maximum number of iterations for this repeatable field
	 *
	 * @return int The maximum number of iterations or -1 if no limit.
	 */
	public final function repeatableUpTo(): int
	{
		return $this->_repeatableUpTo;
	}

	/**
	 * Resets this field's repeat counter
	 */
	public final function resetRepeatCounter()
	{
		$this->_repeatCounter = 0;
	}

	/**
	 * Retrieves the maximal defined repeat index for a repeatable field.
	 *
	 * @return number -1 if no maximal index can be guessed or the actual value
	 */
	public final function getMaxRepeatIndex(): int
	{
		if (!$this->isRepeatable()) {
			return -1;
		}

		$value = ($this->_populatedValue === NULL) ? $this->_defaultValue : $this->_populatedValue;
		if (!is_array($value) || empty($value)) {
			return -1;
		} else {
			return max(array_keys($value));
		}
	}

	/**
	 * Sets or unsets the field as read only
	 *
	 * @param bool $readOnly
	 */
	public final function setReadOnly(bool $readOnly)
	{
		$this->_readOnly = $readOnly;
	}

	/**
	 * Tells if the field is read only
	 *
	 * @return boolean
	 */
	public final function isReadOnly(): bool
	{
		return $this->_readOnly;
	}

	/**
	 * Enables or disables this field
	 *
	 * @param bool $enabled
	 */
	public final function setEnabled(bool $enabled)
	{
		$this->_disabled = !$enabled;
	}

	/**
	 * Tells if the field is enabled
	 *
	 * @return boolean
	 */
	public final function isEnabled(): bool
	{
		return !$this->_disabled;
	}

	/**
	 * Enables of disables value population during field generation
	 *
	 * @param boolean $enabled
	 */
	public final function setGenerationWithValue(bool $enabled)
	{
		$this->_generateWithValue = $enabled;
	}

	/**
	 * Tells if the field should be populated with its value when generated
	 *
	 * @return boolean
	 */
	public final function shouldGenerateWithValue(): bool
	{
		return $this->_generateWithValue;
	}

	/**
	 * Adds extra option to field
	 *
	 * @param string $name Option name
	 * @param string $value Option value
	 */
	public final function addExtra(string $key, string $value)
	{
		$this->_extras[$key] = $value;
	}

	/**
	 * Adds extra options to field
	 *
	 * @param array $extras Associative array whose keys are option names and values are option values
	 */
	public final function addExtras(array $extras)
	{
		foreach ($extras as $k => $v) {
			$this->addExtra($k, $v);
		}
	}

	/**
	 * Tells if the field should be reverted to its default value if it is not populated during validation
	 *
	 * @return boolean
	 */
	public function revertToDefaultIfNotPopulated(): bool
	{
		return false;
	}

	/**
	 * Validates the field's value based on the required flag and the provided filters
	 *
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
			if ($this->_required && ($maxIndex < 0 || $this->_populatedValue === NULL || !is_array($this->_populatedValue))) {
				if (!$silent) {
					trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
				}
				return false;
			}
			if (!$this->applyFiltersOnField($silent)) {
				return false;
			}
			if ($this->_populatedValue !== NULL && is_array($this->_populatedValue)) {
				for ($i = 0; $i <= $maxIndex; $i++) {
					if (array_key_exists($i, $this->_populatedValue)) {
						if ($this->_required && ($this->_populatedValue[$i] === NULL || $this->_populatedValue[$i] === '')) {
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
			if ($this->_required && ($this->_populatedValue === NULL || $this->_populatedValue === '')) {
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
	 *
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return boolean true if the field qualifies for all filters, false either.
	 *         Note:
	 *         This function should be called after having checked to required aspect of the field
	 *         as it potentially uses the default value of the field
	 */
	protected function applyFiltersOnField(bool $silent = false): bool
	{
		if (!empty($this->_filters)) {
			foreach ($this->_filters as $f) {
				$fieldValue = $this->getValue(true);
				if ($f->appliesToField() && ($f->isRequired() || $fieldValue !== NULL) && !$f->validate($this->getName(), $fieldValue, NULL, $silent)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Applies field's filters to the specified value
	 *
	 * @param mixed $value Value to validate
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return boolean true if the value qualifies for all filters, false either.
	 */
	protected function applyFilterOnValue(mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		$canSkipFilters = (!$this->_required && $value === NULL);
		if (!empty($this->_filters)) {
			foreach ($this->_filters as $f) {
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
	 *
	 * @param array $containingGroups Containing groups
	 * @param FieldType $type_override Type to use to override original field type. If NULL, no override. (Defaults to NULL)
	 * @return array
	 */
	public function generate(array $containingGroups = array(), ?FieldType $type_override = NULL): array
	{
		if ($this->_repeatableUpTo > 0 && $this->_repeatCounter + 1 > $this->_repeatableUpTo) {
			throw new DataValidatorException(sprintf(dgettext('mfx', "The field '%s' cannot be repeated more than %d times."), $this->getName(), $this->_repeatableUpTo));
		}

		$name = $this->getName();
		if (!empty($containingGroups)) {
			$name = sprintf('%s[%s%s', implode('[', $containingGroups), $name, str_pad('', count($containingGroups), ']'));
		}

		return array(
			'@mfx/DataValidator/basic_input.twig',
			array(
				'type' => $this->getHTMLType($type_override),
				'name' => $name,
				'required' => $this->isRequired(),
				'readonly' => $this->isReadOnly(),
				'disabled' => !$this->isEnabled(),
				'value' => $this->shouldGenerateWithValue() ? $this->getIndexedValue($this->_repeatCounter, true) : NULL,
				'repeatable' => $this->isRepeatable(),
				'repeat_counter' => $this->_repeatCounter++,
				'suffix' => NULL,
				'extras' => $this->_extras
			)
		);
	}
}

$fieldFolder = dirname(__FILE__) . '/Field';
require_once("{$fieldFolder}/Email.php");
require_once("{$fieldFolder}/Word.php");
require_once("{$fieldFolder}/Integer.php");
require_once("{$fieldFolder}/WithOptions.php");
require_once("{$fieldFolder}/TextArea.php");
require_once("{$fieldFolder}/CheckBox.php");
require_once("{$fieldFolder}/DateTime.php");
require_once("{$fieldFolder}/Password.php");
require_once("{$fieldFolder}/File.php");
