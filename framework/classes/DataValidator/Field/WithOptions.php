<?php
/**
 * Data validation field with multiple options type class
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Field;

use CheeseBurgames\MFX\DataValidator\Field;
use CheeseBurgames\MFX\DataValidator\FieldType;

/**
 * Descriptor of a field type with multiple options (such as 'select' or 'radio' types)
 */
class WithOptions extends Field
{
	/**
	 * @var array Options holder
	 */
	private $_options = NULL;
	
	/**
	 * Add an option to the list
	 * @param string $label Option label 
	 * @param string $value Option value. Equals to label if NULL. (Defaults to NULL)
	 */
	public function addOption($label, $value = NULL) {
		if ($value === NULL)
			$value = $label;
		
		if ($this->_options === NULL)
			$this->_options = array(array( 'value' => $value, 'label' => $label ));
		else
			$this->_options[] = array( 'value' => $value, 'label' => $label );
	}
	
	/**
	 * Add options to the list
	 * @param array $options Options array. Items may be mixed scalar values and arrays. If an item is an array, it should have keys 'value' and 'label'.
	 * @param bool $useAsKeyValueStore If set, items for the $options array that are not arrays themselves will be considered as key/value pairs. If not set, item value will be used for label and value. (Defaults to false) 
	 */
	public function addOptions(array $options, $useAsKeyValueStore = false) {
		array_walk($options, function(&$item, $key, $useAsKeyValueStore) {
			if (!is_array($item))
				$item = array( 'value' => ($useAsKeyValueStore || is_string($key)) ? $key : $item, 'label' => $item );
		}, $useAsKeyValueStore);
		
		if ($this->_options === NULL)
			$this->_options = $options;
		else
			$this->_options = array_merge($this->_options, $options);
	}

	/**
	 * (non-PHPdoc)
	 * @see DataValidator_Field::validate()
	 */
	public function validate() {
		if ($this->isEnabled() == false)
			return true;
		
		$value = $this->getValue();
		
		// Checks value against required status
		if ($this->isRequired() && empty($value))
		{
			trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
			return false;
		}
		
		if ($this->isRepeatable())
		{
			// Checks value against required status
			if (!is_array($value))
			{
				trigger_error(sprintf(dgettext('mfx', "The field '%s' is required."), $this->getName()));
				return false;
			}
			
			// Validates through filters
			if (!$this->applyFiltersOnField())
				return false;
		
			$maxIndex = $this->getMaxRepeatIndex();
			for ($i = 0; $i <= $maxIndex; $i++)
			{
				$val = $this->getIndexedValue($i);
				
				// Checks value against required status
				if ($this->isRequired() && empty($val))
				{
					trigger_error(sprintf(dgettext('mfx', "The field '%s' at index %d is required."), $this->getName(), $i));
					return false;
				}
				
				if (!is_array($val))
					$val = array($val);
				
				// Checks value against options and applies filters
				foreach ($val as $v)
				{
					if (!$this->_isValidOption($v))
					{
						trigger_error(sprintf(dgettext('mfx', "'%s' is not a valid value for the '%s' field at index %d."), $v, $this->getName(), $i));
						return false;
					}
						
					if (!$this->applyFilterOnValue($v, $i))
						return false;
				}
			}
		}
		else
		{
			// Validates through filters
			if (!$this->applyFiltersOnField())
				return false;
		
			if (!is_array($value))
				$value = array($value);
			
			// Checks value against options and applies filters
			foreach ($value as $v)
			{
				if (!$this->_isValidOption($v))
				{
					trigger_error(sprintf(dgettext('mfx', "'%s' is not a valid value for the '%s' field."), $v, $this->getName()));
					return false;
				}
				
				if (!$this->applyFilterOnValue($v))
					return false;
			}
		}
		return true;
	}
	
	/**
	 * Tells if the specified value is a valid option, based on the list.
	 * @param mixed $value
	 * @return boolean true if the value is a valid option, false either.
	 */
	private function _isValidOption($value) {
		if ($this->_options !== NULL)
		{
			foreach ($this->_options as $opt)
			{
				if ($opt['value'] == $value)
					return true;
			}
		}
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::getHTMLType()
	 * @param FieldType $type_override
	 */
	public function getHTMLType(FieldType $type_override = NULL) {
		return ($this->getType() === FieldType::MULTI_SELECT) ? 'select' : parent::getHTMLType($type_override);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Field::generate()
	 * @param array $containingGroups
	 * @param FieldType $type_override
	 */
	public function generate(array $containingGroups = array(), FieldType $type_override = NULL) {
		$template = ($this->getType()->equals(FieldType::RADIO)) ? '@mfx/DataValidator/radio.twig' : '@mfx/DataValidator/select.twig';
		
		$result = parent::generate($containingGroups, $type_override);
		if ($type_override === NULL || !$type_override->equals(FieldType::HIDDEN)) {
			$result[0] = $template;
			$result[1] = array_merge($result[1], array(
					'name' => $this->getType()->equals(FieldType::MULTI_SELECT) ? sprintf('%s[]', $result[1]['name']) : $result[1]['name'],
					'multiple' => $this->getType()->equals(FieldType::MULTI_SELECT),
					'options' => $this->_options
			));
		}
		return $result;
	}
}

FieldType::registerClassForType(new FieldType(FieldType::SELECT), __NAMESPACE__ . '\WithOptions');
FieldType::registerClassForType(new FieldType(FieldType::MULTI_SELECT), __NAMESPACE__ . '\WithOptions');
FieldType::registerClassForType(new FieldType(FieldType::RADIO), __NAMESPACE__ . '\WithOptions');
