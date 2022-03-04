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
	private string $_message;

	/**
	 * @var IMessageDispatcher Overridden message dispatcher
	 */
	private ?IMessageDispatcher $_messageDispatcher = NULL;

	/**
	 * Constructor
	 * @param string $message Error message
	 */
	public function __construct(string $message = NULL)
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
	 * @param int $level Error level (Defaults to E_USER_NOTICE)
	 */
	protected final function emitMessage(string $fieldName, int $level = E_USER_NOTICE)
	{
		if (!empty($this->_message)) {
			$msg = sprintf($this->_message, $fieldName);
			if ($this->_messageDispatcher === null) {
				trigger_error($msg, $level);
			} else {
				$this->_messageDispatcher->dispatchMessage($msg, $level);
			}
		}
	}

	/**
	 * Sets the error message.
	 * 
	 * If empty or not a string, no message will be dispatched.
	 * 
	 * @param string $message
	 */
	protected final function setMessage(string $message)
	{
		if (is_string($message) && !empty($message)) {
			$this->_message = $message;
		} else {
			$this->_message = null;
		}
	}

	/**
	 * Overriddes the default message dispatcher
	 * @param IMessageDispatcher $dispatcher
	 */
	public final function setMessageDispatcher(IMessageDispatcher $dispatcher)
	{
		$this->_messageDispatcher = $dispatcher;
	}

	/**
	 * Tells if this filter can be skipped during the validation process if the field is not required and has no value.
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @return boolean
	 * 
	 * Note:
	 * This function is ignored for filters returning true in appliesToField().
	 */
	public function mayBeSkipped(int $atIndex = -1): bool
	{
		return true;
	}

	/**
	 * Tells if this filter must be applied to the field's values or to the field instance only
	 * @return boolean
	 */
	public function appliesToField(): bool
	{
		return false;
	}

	/**
	 * Validates value
	 * @param string $fieldName Field name
	 * @param mixed $value Value to validate
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 * @return bool true if the filter validates, false either
	 */
	abstract public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool;
}
