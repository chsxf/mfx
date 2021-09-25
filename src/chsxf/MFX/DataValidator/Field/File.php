<?php
/**
 * Data validation Checkbox field type class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
namespace chsxf\MFX\DataValidator\Field;

use chsxf\MFX\DataValidator\Field;
use chsxf\MFX\DataValidator\FieldType;

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
	 * {@inheritdoc}
	 * @see \chsxf\MFX\DataValidator\Field::setValue()
	 */
	public function setValue($value) {
		if ($this->isRepeatable()) {
			if (is_array($value)) {
				$value = array_filter($value, function ($item) {
					return isset($item['error']) && $item['error'] != UPLOAD_ERR_NO_FILE;
				});
			}
			parent::setValue($value);
		}
		else {
			if (isset($value['error']) && $value['error'] != UPLOAD_ERR_NO_FILE) {
				parent::setValue($value);
			}
		}
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