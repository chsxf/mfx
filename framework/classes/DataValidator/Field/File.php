<?php
/**
 * Data validation Checkbox field type class
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */
namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

/**
 * Descriptor of a checkbox field type
 */
class File extends Field {
	private static $_requiredKeys = array(
			'name',
			'type',
			'tmp_name',
			'error',
			'size'
	);

	/**
	 * Constructor
	 *
	 * @param string $name Field's name
	 * @param FieldType $type Field's type
	 * @param mixed $defaultValue Field's default value
	 * @param boolean $required If set, this field will become required in the validation process.
	 */
	protected function __construct($name, FieldType $type, $defaultValue, $required) {
		parent::__construct($name, $type, empty($defaultValue) ? 0 : $defaultValue, $required);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Field::validate()
	 */
	public function validate($silent = false) {
		if (!parent::validate($silent)) {
			return false;
		}

		$error = dgettext('mfx', "The field '%s' does not contain a valid file.");
		$errorRepeatable = dgettext('mfx', "The field '%s' at index %d does not contain a valid file.");

		if ($this->isRepeatable()) {
			$maxIndex = $this->getMaxRepeatIndex();
			for ($i = 0; $i <= $maxIndex; $i++) {
				$value = $this->getIndexedValue($i, true);
				if (($this->isRequired() || !empty($value)) && !$this->_validateFileData($value)) {
					if (empty($silent)) {
						trigger_error(sprintf($errorRepeatable, $this->getName(), $i));
					}
					return false;
				}
			}
		}
		else {
			$value = $this->getValue(true);
			if (($this->isRequired() || !empty($value)) && !$this->_validateFileData($value)) {
				if (empty($silent)) {
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
	 * @param mixed $_data
	 */
	private function _validateFileData($_data) {
		$isValid = false;
		if (is_array($_data)) {
			$intersect = array_intersect(self::$_requiredKeys, array_keys($_data));
			$isValid = (count($intersect) == count(self::$_requiredKeys));

			if ($isValid) {
				$isValid = ($_data['error'] == 0);
			}
		}
		return $isValid;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Field::revertToDefaultIfNotPopulated()
	 */
	public function revertToDefaultIfNotPopulated() {
		return true;
	}

}

FieldType::registerClassForType(new FieldType(FieldType::FILE), File::class);