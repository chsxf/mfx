<?php

/**
 * Database management helpers
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\PDO\DatabaseManager as PDODatabaseManager;
use chsxf\PDO\DatabaseManagerException;

/**
 * Database manager class
 * @since 1.0
 */
final class DatabaseManager extends PDODatabaseManager
{
    public const DEFAULT_CONNECTION = '__default';

    /**
     * @var array Open connections container
     */
    private static array $openConnections = array();

    private string $serverConfigurationKey;

    /**
     * Constructor
     * @since 1.0
     * @param string $dsn Data Source Name (ie mysql:host=localhost;dbname=mydb)
     * @param string $username Username
     * @param string $password Password
     * @param string $server Server configuration key
     * @see \PDO::__construct()
     */
    public function __construct(string $dsn, string $username, string $password, string $server = self::DEFAULT_CONNECTION)
    {
        parent::__construct($dsn, $username, $password, null, Config::get(ConfigConstants::DATABASE_ERROR_LOGGING, false));
        $this->serverConfigurationKey = $server;
    }

    /**
     * Opens a connection to a database server, or returns the currently active connection to this server
     * @since 1.0
     * @param string $server Server configuration key (Defaults to __default).
     * @param bool $forceNew If set, a new connection is open even if a previous similar one exists in the cache (Defaults to false)
     * @throws DatabaseManagerException If no configuration is available nor valid for this server key
     * @return DatabaseManager
     */
    public static function open(string $server = self::DEFAULT_CONNECTION, bool $forceNew = false): DatabaseManager
    {
        if (array_key_exists($server, self::$openConnections) && empty($forceNew)) {
            return self::$openConnections[$server];
        }

        if (!Config::has(ConfigConstants::DATABASE_SERVERS)) {
            throw new DatabaseManagerException("No database server configured.");
        }

        $serverConfig = Config::get(ConfigConstants::DATABASE_SERVERS . ".{$server}");
        if (is_string($serverConfig) && !empty($serverConfig)) {
            $serverConfig = Config::get(ConfigConstants::DATABASE_SERVERS . ".{$serverConfig}");
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

        $dbm = new DatabaseManager($dsn, $username, $password, $server);
        if (!array_key_exists($server, self::$openConnections)) {
            self::$openConnections[$server] = $dbm;
        }
        return $dbm;
    }

    /**
     * @since 1.0
     * @param DatabaseManager $_manager
     */
    public static function close(DatabaseManager &$_manager)
    {
        unset(self::$openConnections[$_manager->serverConfigurationKey]);
        $_manager = null;
    }
}
