<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

use chsxf\MFX\DatabaseConnectionInstance;

/**
 * Database service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IDatabaseService
{
    /**
     * Default connection name constant
     */
    final public const DEFAULT_CONNECTION = '__default';

    /**
     * Opens a connection to a database server, or returns the currently active connection to this server
     * @param string $server Server configuration key (Defaults to __default).
     * @param bool $forceNew If set, a new connection is open even if a previous similar one exists in the cache (Defaults to false)
     * @return DatabaseConnectionInstance
     */
    public function open(string $server = self::DEFAULT_CONNECTION, bool $forceNew = false): DatabaseConnectionInstance;

    /**
     * Attempts to close a connection instance.
     * The connection instance variable is passed as a reference and will be null after the call to this function.
     * Please note that the connection will only be closed after all references to it have been released.
     * You're responsible for cleaning your own additional references if existing.
     * @param DatabaseConnectionInstance $connectionInstance
     */
    public function close(DatabaseConnectionInstance &$connectionInstance);
}
