<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

use ArrayAccess;

/**
 * Session service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface ISessionService extends ArrayAccess
{
    /**
     * Sets a set of values in the current session
     * @param array $values Associative array of values to set
     */
    public function setInSession(array $values): void;

    /**
     * Unsets a set of values in the current session
     * @param string[] $keys List of value keys to unset
     */
    public function unsetInSession(string ...$keys): void;
}
