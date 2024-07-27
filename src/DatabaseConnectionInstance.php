<?php

namespace chsxf\MFX;

use chsxf\PDO\DatabaseManager as PDODatabaseManager;

/**
 * Instance of a database connection
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
class DatabaseConnectionInstance extends PDODatabaseManager
{
    private readonly string $serverConfigurationKey;

    /**
     * Constructor
     * @param string $serverConfigurationKey Server configuration key (matches the server keys declared in the configuration directives)
     * @param string $dsn Data Source Name (ie mysql:host=localhost;dbname=mydb)
     * @param string $username Username
     * @param string $password Password
     * @param array $options Driver options
     * @param bool $useDatabaseErrorLogging Is set, errors will be logged in the database. False by default
     *
     * @see chsxf\PDO\DatabaseManager::__construct()
     */
    public function __construct(string $serverConfigurationKey, string $dsn, ?string $username = null, ?string $password = null, ?array $options = null, bool $useDatabaseErrorLogging = false)
    {
        parent::__construct($dsn, $username, $password, $options, $useDatabaseErrorLogging);
        $this->serverConfigurationKey = $serverConfigurationKey;
    }

    /**
     * Returns the server configuration key
     * @return string 
     */
    public function getServerConfigurationKey()
    {
        return $this->serverConfigurationKey;
    }
}
