<?php
/**
 * Data validation field filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Abstract data validation filter class
 * 
 * All filters must inherit from this class.
 */
abstract class AbstractFilter
{
	/**
	 * @var string Filter error message
	 */
	private $_message;
	
	/**
	 * @var IMessageDispatcher Overridden message dispatcher
	 */
	private $_messageDispatcher = NULL;
	
	/**
	 * Constructor
	 * @param string $message Error message
	 */
	public function __construct($message = NULL)
	{
		$this->setMessage($message);
	}
	
	/**
	 * Dispatches the error message on demand
	 * 
	 * The message string should contain a string placeholder %s so the function can replace it at runtime.
	 * @see sprintf()
	 * 
	 * @param string $fieldName Field name to which this message applies
	 * @param string $level Error level (Defaults to E_USER_NOTICE)
	 */
	protected final function emitMessage($fieldName, $level = E_USER_NOTICE) {
		if (!empty($this->_message)) {
			$msg = sprintf($this->_message, $fieldName);
			if ($this->_messageDispatcher === NULL)
				trigger_error($msg, $level);
			else
				$this->_messageDispatcher->dispatchMessage($msg, $level);
		}
	}
	
	/**
	 * Sets the error message.
	 * 
	 * If empty or not a string, no message will be dispatched.
	 * 
	 * @param string $message
	 */
	protected final function setMessage($message) {
		if (is_string($message) && !empty($message))
			$this->_message = $message;
		else
			$this->_message = NULL;
	}
	
	/**
	 * Overriddes the default message dispatcher
	 * @param IMessageDispatcher $dispatcher
	 */
	public final function setMessageDispatcher(IMessageDispatcher $dispatcher) {
		$this->_messageDispatcher = $dispatcher;
	}
	
	/**
	 * Tells if this filter can be skipped during the validation process if the field is not required and has no value.
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @return boolean
	 * 
	 * Note:
	 * This function is ignored for filters returning true in appliesToField().
	 */
	public function mayBeSkipped($atIndex = NULL) {
		return true;
	}
	
	/**
	 * Tells if this filter must be applied to the field's values or to the field instance only
	 * @return boolean
	 */
	public function appliesToField() {
		return false;
	}
	
	/**
	 * Validates value
	 * @param string $fieldName Field name
	 * @param mixed $value Value to validate
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return bool true if the filter validates, false either
	 */
	abstract public function validate($fieldName, $value, $atIndex = NULL, $silent = false);
}
