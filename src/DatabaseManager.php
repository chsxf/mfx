<?php

namespace chsxf\MFX;

use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\IDatabaseService;
use chsxf\PDO\DatabaseManagerException;

/**
 * Database manager class, acting as the default database service implementation
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class DatabaseManager implements IDatabaseService
{
    private array $openConnections;

    /**
     * Constructor
     * @since 2.0
     * @param IConfigService $configService
     */
    public function __construct(private readonly IConfigService $configService)
    {
        $this->openConnections = [];
    }

    /**
     * Opens a connection to a database server, or returns the currently active instance to this server
     * @since 2.0
     * @param string $server Server configuration key (Defaults to __default).
     * @param bool $forceNew If set, a new connection is open even if a previous similar one exists in the cache (Defaults to false)
     * @throws DatabaseManagerException If no configuration is available nor valid for this server key
     * @return DatabaseConnectionInstance
     */
    public function open(string $server = self::DEFAULT_CONNECTION, bool $forceNew = false): DatabaseConnectionInstance
    {
        if (array_key_exists($server, $this->openConnections) && empty($forceNew)) {
            return $this->openConnections[$server];
        }

        if (!$this->configService->hasValue(ConfigConstants::DATABASE_SERVERS)) {
            throw new DatabaseManagerException("No database server configured.");
        }

        $serverConfig = $this->configService->getValue(ConfigConstants::DATABASE_SERVERS . ".{$server}");
        if (is_string($serverConfig) && !empty($serverConfig)) {
            $serverConfig = $this->configService->getValue(ConfigConstants::DATABASE_SERVERS . ".{$serverConfig}");
        }
        if (empty($serverConfig) || !is_array($serverConfig)) {
            throw new DatabaseManagerException("No server can be found for the '{$server}' key.");
        }

        foreach (array(
            'dsn',
            'username',
            'password'
        ) as $p) {
            if (empty($serverConfig[$p])) {
                throw new DatabaseManagerException("Unable to find the '{$p}' parameter for database server '{$server}'.");
            }
            $$p = $serverConfig[$p];
        }

        $dbm = new DatabaseConnectionInstance($server, $dsn, $username, $password, null, $this->configService->getValue(ConfigConstants::DATABASE_ERROR_LOGGING, false));
        if (!array_key_exists($server, $this->openConnections)) {
            $this->openConnections[$server] = $dbm;
        }
        return $dbm;
    }

    /**
     * Closes a database server connection
     * @since 2.0
     * @param DatabaseConnectionInstance $connectionInstance Reference to a database connection instance
     */
    public function close(DatabaseConnectionInstance &$connectionInstance)
    {
        unset($this->openConnections[$connectionInstance->getServerConfigurationKey()]);
        $_manager = null;
    }
}
