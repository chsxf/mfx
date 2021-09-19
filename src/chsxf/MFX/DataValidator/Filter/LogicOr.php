<?php
/**
 * Data validator "OR" filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;
use chsxf\MFX\DataValidator\IMessageDispatcher;

/**
 * Descriptor of a field filter applying a basic "OR" logic operation on other filters
 * 
 * This filter validates as soon as a contained filter validates.
 */
class LogicOr extends AbstractFilter implements IMessageDispatcher {
	
	/**
	 * Filters container
	 * @var array
	 */
	private $_filters = array();
	
	/**
	 * Messages container
	 * @var array
	 */
	private $_messages = array();
	
	/**
	 * Maximum message level
	 * @var int
	 */
	private $_messageLevel = 0;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Adds a filter
	 * @param AbstractFilter $filter
	 */
	public function addFilter(AbstractFilter $filter) {
		$filter->setMessageDispatcher($this);
		$this->_filters[] = $filter;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		foreach ($this->_filters as $filter) {
			if ($filter->validate($fieldName, $value, $atIndex, $silent))
				return true;
		}
		
		if (empty($silent)) {
			$message = implode(sprintf('<br />%d<br />', dgettext('mfx', ' or ')), $this->_messages);
			$this->emitMessage($message, $this->_messageLevel);
		}
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IMessageDispatcher::dispatchMessage()
	 */
	public function dispatchMessage($message, $level) {
		$this->_messages[] = $message;
		$this->_messageLevel = max($this->_messageLevel, $level);
	}
	
}