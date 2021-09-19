<?php
/**
 * Data validation field class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */
namespace chsxf\MFX\DataValidator;

/**
 * Descriptor of a data validator field
 */
class Field {

	/**
	 * @var string Field's name
	 */
	private $_name;

	/**
	 * @var FieldType Field's type
	 */
	private $_type;

	/**
	 * @var boolean If set, this field is required.
	 */
	private $_required;

	/**
	 * @var mixed Field's default value
	 */
	private $_defaultValue;

	/**
	 * @var mixed Field's populated value
	 */
	private $_populatedValue;

	/**
	 * @var array Field's filters holder
	 */
	private $_filters;

	/**
	 * @var bool If set, this field is repeatable.
	 */
	private $_isRepeatable;

	/**
	 * @var int Maximum number of iteration for this field. If NULL, no limit.
	 */
	private $_repeatableUpTo;

	/**
	 * @var int Current repeat counter for the generator.
	 */
	private $_repeatCounter;

	/**
	 * @var bool Read only flag
	 */
	private $_readOnly;

	/**
	 * @var bool Disabled flag
	 */
	private $_disabled;

	/**
	 * @var boolean If set, the field is populated with the current or default value when generated
	 */
	private $_generateWithValue;

	/**
	 * @var array Extra options for field generation
	 */
	private $_extras;

	/**
	 * Constructor
	 *
	 * @param String $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field becomes required
	 * @throws DataValidatorException If $name is empty or not a string
	 */
	protected function __construct($name, FieldType $type, $defaultValue, $required) {
		if (empty($name) && $name !== '0')
			throw new DataValidatorException(dgettext('mfx', "Field name cannot be empty."));
		if (!is_string($name))
			throw new DataValidatorException(dgettext('mfx', "Expected string as the field name."));

		$this->_name = $name;
		$this->_type = $type;
		$this->_defaultValue = $defaultValue;
		$this->_required = !empty($required);

		$this->_populatedValue = NULL;

		$this->_filters = NULL;

		$this->_isRepeatable = false;
		$this->_repeatableUpTo = NULL;
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
	public static function create($name, FieldType $type, $defaultValue = NULL, $required = true) {
		$class = FieldType::getClassForType($type);
		return new $class($name, $type, $defaultValue, $required);
	}

	/**
	 * Gets the name of this field
	 *
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Gets the type of this field
	 *
	 * @return FieldType
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * Gets the HTML type of this field
	 *
	 * @param FieldType $type_override Type to use to override original field type. If NULL, no override. (Defaults to NULL)
	 * @return string
	 */
	public function getHTMLType(FieldType $type_override = NULL) {
		if ($type_override !== NULL)
			return $type_override->value();
		return $this->_type->value();
	}

	/**
	 * Tells if this field is required or not
	 *
	 * @return boolean
	 */
	public function isRequired() {
		return $this->_required;
	}

	/**
	 * Tells if this field as a default value or not
	 *
	 * @return boolean
	 */
	public function hasDefaultValue() {
		return ($this->getDefaultValue() !== NULL);
	}

	/**
	 * Get this field's default value
	 *
	 * @return mixed
	 */
	public function getDefaultValue() {
		return $this->_defaultValue;
	}

	/**
	 * Sets this field's value
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		if ($this->isRepeatable()) {
			if (!is_array($value))
				throw new DataValidatorException(sprintf(dgettext('mfx', "Value for repeatable field '%s' must be an array."), $this->getName()));

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
	public function getValue($returnDefaultIfNotSet = false) {
		if ($this->_populatedValue !== NULL)
			return $this->_populatedValue;
		else
			return $returnDefaultIfNotSet ? $this->_defaultValue : NULL;
	}

	/**
	 * Get a indexed value from this field if repeatable
	 *
	 * @param int $index Index of the value to retrieve
	 * @param bool $returnDefaultIfNotSet If set, the function returns the default value if the field has not been populated yet.
	 * @return mixed the indexed value or the field's value if the field is not repeatable.
	 */
	public function getIndexedValue($index, $returnDefaultIfNotSet = false) {
		if (!$this->isRepeatable())
			return $this->getValue($returnDefaultIfNotSet);

		if ($this->_populatedValue === NULL || (is_array($this->_populatedValue) && !array_key_exists($index, $this->_populatedValue))) {
			if ($returnDefaultIfNotSet) {
				if (is_array($this->_defaultValue)) {
					if (array_key_exists($index, $this->_defaultValue))
						return $this->_defaultValue[$index];
				}
				else
					return $this->_defaultValue;
			}
			return NULL;
		}
		else {
			if (!is_array($this->_populatedValue))
				return $this->_populatedValue;
			else
				return $this->_populatedValue[$index];
		}
	}

	/**
	 * Adds a validation filter to this field
	 *
	 * @param AbstractFilter $filter
	 */
	public final function addFilter(AbstractFilter $filter) {
		if ($this->_filters === NULL)
			$this->_filters = array();
		$this->_filters[] = $filter;
	}

	/**
	 * Removes a validation filter from this field
	 *
	 * @param AbstractFilter $filter
	 */
	public final function removeFilter(AbstractFilter $filter) {
		if (!empty($this->_filters)) {
			$key = array_search($filter, $this->_filters, true);
			if ($key !== false)
				array_splice($this->_filters, $key, 1);
		}
	}

	/**
	 * Sets or unsets the field as repeatable
	 *
	 * @param bool $isRepeatable If set, the field becomes repeatable
	 * @param int $upTo Maximum number of iteration. If NULL, 0 or negative, no limit is applied. (Defaults to NULL)
	 */
	public final function setRepeatable($isRepeatable, $upTo = NULL) {
		$this->_isRepeatable = !empty($isRepeatable);
		if ($upTo === NULL || !$this->_isRepeatable)
			$this->_repeatableUpTo = NULL;
		else {
			$upTo = intval($upTo);
			$this->_repeatableUpTo = ($upTo < 0) ? NULL : $upTo;
		}
	}

	/**
	 * Tells if this field is repeatable
	 *
	 * @return boolean
	 */
	public final function isRepeatable() {
		return $this->_isRepeatable;
	}

	/**
	 * Tells the maximum number of iterations for this repeatable field
	 *
	 * @return int The maximum number of iterations or NULL if no limit.
	 */
	public final function repeatableUpTo() {
		return $this->_repeatableUpTo;
	}

	/**
	 * Resets this field's repeat counter
	 */
	public final function resetRepeatCounter() {
		$this->_repeatCounter = 0;
	}

	/**
	 * Retrieves the maximal defined repeat index for a repeatable field.
	 *
	 * @return number -1 if no maximal index can be guessed or the actual value
	 */
	public final function getMaxRepeatIndex() {
		if (!$this->isRepeatable())
			return -1;

		$value = ($this->_populatedValue === NULL) ? $this->_defaultValue : $this->_populatedValue;
		if (!is_array($value) || empty($value))
			return -1;
		else
			return max(array_keys($value));
	}

	/**
	 * Sets or unsets the field as read only
	 *
	 * @param bool $readOnly
	 */
	public final function setReadOnly($readOnly) {
		$this->_readOnly = !empty($readOnly);
	}

	/**
	 * Tells if the field is read only
	 *
	 * @return boolean
	 */
	public final function isReadOnly() {
		return $this->_readOnly;
	}

	/**
	 * Enables or disables this field
	 *
	 * @param bool $enabled
	 */
	public final function setEnabled($enabled) {
		$this->_disabled = empty($enabled);
	}

	/**
	 * Tells if the field is enabled
	 *
	 * @return boolean
	 */
	public final function isEnabled() {
		return !$this->_disabled;
	}

	/**
	 * Enables of disables value population during field generation
	 *
	 * @param boolean $enabled
	 */
	public final function setGenerationWithValue($enabled) {
		$this->_generateWithValue = !empty($enabled);
	}

	/**
	 * Tells if the field should be populated with its value when generated
	 *
	 * @return boolean
	 */
	public final function shouldGenerateWithValue() {
		return $this->_generateWithValue;
	}

	/**
	 * Adds extra option to field
	 *
	 * @param string $name Option name
	 * @param string $value Option value
	 */
	public final function addExtra($key, $value) {
		$this->_extras[$key] = $value;
	}

	/**
	 * Adds extra options to field
	 *
	 * @param array $extras Associative array whose keys are option names and values are option values
	 */
	public final function addExtras(array $extras) {
		foreach ($extras as $k => $v)
			$this->addExtra($k, $v);
	}

	/**
	 * Tells if the field should be reverted to its default value if it is not populated during validation
	 *
	 * @return boolean
	 */
	public function revertToDefaultIfNotPopulated() {
		return false;
	}

	/**
	 * Validates the field's value based on the required flag and the provided filters
	 *
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return boolean
	 */
	public function validate($silent = false) {
		if ($this->isEnabled() == false)
			return true;

		if ($this->isRepeatable()) {
			$maxIndex = $this->getMaxRepeatIndex();
			if ($this->_required && ($maxIndex < 0 || $this->_populatedValue === NULL || !is_array($this->_populatedValue))) {
				if (empty($silent))
					trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
				return false;
			}
			if (!$this->applyFiltersOnField($silent))
				return false;
			if ($this->_populatedValue !== NULL && is_array($this->_populatedValue)) {
				for ($i = 0; $i <= $maxIndex; $i++) {
					if (array_key_exists($i, $this->_populatedValue)) {
						if ($this->_required && ($this->_populatedValue[$i] === NULL || $this->_populatedValue[$i] === '')) {
							if (empty($silent))
								trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is required."), $this->getName(), $i));
							return false;
						}
					}

					$value = $this->getIndexedValue($i);
					if (!$this->applyFilterOnValue($value, $i, $silent))
						return false;
				}
			}
			return true;
		}
		else {
			if ($this->_required && ($this->_populatedValue === NULL || $this->_populatedValue === '')) {
				if (empty($silent))
					trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
				return false;
			}

			if (!$this->applyFiltersOnField($silent))
				return false;

			// Filters
			$value = $this->getValue(true);
			return $this->applyFilterOnValue($value, NULL, $silent);
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
	protected function applyFiltersOnField($silent = false) {
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
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return boolean true if the value qualifies for all filters, false either.
	 */
	protected function applyFilterOnValue($value, $atIndex = NULL, $silent = false) {
		$canSkipFilters = (!$this->_required && $value === NULL);
		if (!empty($this->_filters)) {
			foreach ($this->_filters as $f) {
				if ($f->appliesToField() || ($canSkipFilters && $f->mayBeSkipped($atIndex)))
					continue;
				if (!$f->validate($this->getName(), $value, $atIndex, $silent))
					return false;
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
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		if ($this->_repeatableUpTo !== NULL && $this->_repeatCounter + 1 > $this->_repeatableUpTo)
			throw new DataValidatorException(sprintf(dgettext('mfx', "The field '%s' cannot be repeated more than %d times."), $this->getName(), $this->_repeatableUpTo));

		$name = $this->getName();
		if (!empty($containingGroups))
			$name = sprintf('%s[%s%s', implode('[', $containingGroups), $name, str_pad('', count($containingGroups), ']'));

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

require_once ('DataValidator/Field/Email.php');
require_once ('DataValidator/Field/Word.php');
require_once ('DataValidator/Field/Integer.php');
require_once ('DataValidator/Field/WithOptions.php');
require_once ('DataValidator/Field/TextArea.php');
require_once ('DataValidator/Field/CheckBox.php');
require_once ('DataValidator/Field/DateTime.php');
require_once ('DataValidator/Field/Password.php');
require_once ('DataValidator/Field/File.php');