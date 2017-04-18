<?php
/**
 * Data validator "in list" field filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filter;

use CheeseBurgames\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field checking presence of the value in a list
 */
class In extends AbstractFilter
{
	/**
	 * @var array List of options
	 */
	private $_options;
	
	/**
	 * Constructor
	 * @param array $options List of options
	 * @param unknown $message Error message
	 */
	public function __construct(array $options, $message = NULL) {
		if ($message === NULL)
			$message = sprintf(dgettext('mfx', "The value of the '%%s' field must be one of the following values : %s"), implode(', ', $options));
		parent::__construct($message);
		
		$this->_options = $options;
	}
	
	/**
	 * {@inheritDoc}
	 * @see AbstractFilter::validate()
	 */
	public function validate($fieldName, $value, $atIndex = NULL) {
		if (!in_array($value, $this->_options)) {
			$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
	
}