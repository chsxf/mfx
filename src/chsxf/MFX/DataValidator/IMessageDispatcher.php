<?php

/**
 * Data validator message dispatcher interface
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Interface describing data validator's message dispatchers
 * @since 1.0
 */
interface IMessageDispatcher
{
    /**
     * Dispatches a message
     * @since 1.0
     * @param string $message Message to dispatch
     * @param int $level Error level of the message to dispatch
     */
    public function dispatchMessage(string $message, int $level);
}
