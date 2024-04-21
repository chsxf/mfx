<?php

/**
 * Data validator "OR" filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;
use chsxf\MFX\DataValidator\IMessageDispatcher;

/**
 * Descriptor of a field filter applying a basic "OR" logic operation on other filters
 * 
 * This filter validates as soon as a contained filter validates.
 */
class LogicOr extends AbstractFilter implements IMessageDispatcher
{

	/**
	 * Filters container
	 * @var array
	 */
	private array $_filters = array();

	/**
	 * Messages container
	 * @var array
	 */
	private array $_messages = array();

	/**
	 * Maximum message level
	 * @var int
	 */
	private int $_messageLevel = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Adds a filter
	 * @param AbstractFilter $filter
	 */
	public function addFilter(AbstractFilter $filter)
	{
		$filter->setMessageDispatcher($this);
		$this->_filters[] = $filter;
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		foreach ($this->_filters as $filter) {
			if ($filter->validate($fieldName, $value, $atIndex, $silent)) {
				return true;
			}
		}

		if (!$silent) {
			$message = implode(sprintf('<br />%d<br />', dgettext('mfx', ' or ')), $this->_messages);
			$this->emitMessage($message, $this->_messageLevel);
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see IMessageDispatcher::dispatchMessage()
	 */
	public function dispatchMessage(string $message, int $level)
	{
		$this->_messages[] = $message;
		$this->_messageLevel = max($this->_messageLevel, $level);
	}
}
