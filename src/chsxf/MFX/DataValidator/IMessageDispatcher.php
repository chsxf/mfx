<?php
/**
 * Data validator message dispatcher interface
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Interface describing data validator's message dispatchers
 */
interface IMessageDispatcher {
	
	/**
	 * Dispatches a message
	 * @param string $message Message to dispatch
	 * @param int $level Error level of the message to dispatch
	 */
	function dispatchMessage(string $message, int $level);
	
}