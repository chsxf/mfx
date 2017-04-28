<?php
/**
 * Data validator file path based field filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filter;

use CheeseBurgames\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a field filter based on file path
 */
class Path extends AbstractFilter {
	
	/**
	 * @var string Root path holder
	 */
	private $_root;
	
	/**
	 * Constructor
	 * @param string $root Root path to look file into. Defaults to current working directory.
	 * @param string $message Error message
	 */
	public function __construct($root = './', $message = NULL) {
		if ($message == NULL)
			$message = dgettext('mfx', "The '%s' field does not contain an existing path.");
		parent::__construct($message);
		
		$this->_root = $root;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		$fp = $this->_root . $value;
		if (!file_exists($fp)) {
			if (empty($silent))
				$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
}