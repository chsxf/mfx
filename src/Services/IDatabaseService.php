<?php

namespace chsxf\MFX\Services;

use chsxf\MFX\DatabaseConnectionInstance;

interface IDatabaseService
{
    final public const DEFAULT_CONNECTION = '__default';

    /**
     * Opens a connection to a database server, or returns the currently active connection to this server
     * @since 2.0
     * @param string $server Server configuration key (Defaults to __default).
     * @param bool $forceNew If set, a new connection is open even if a previous similar one exists in the cache (Defaults to false)
     * @throws DatabaseManagerException If no configuration is available nor valid for this server key
     * @return DatabaseConnectionInstance
     */
    public function open(string $server = self::DEFAULT_CONNECTION, bool $forceNew = false): DatabaseConnectionInstance;

    /**
     * @since 2.0
     * @param DatabaseConnectionInstance $connectionInstance
     */
    public function close(DatabaseConnectionInstance &$connectionInstance);
}
