<?php

namespace chsxf\MFX;

use chsxf\PDO\DatabaseManager as PDODatabaseManager;

class DatabaseConnectionInstance extends PDODatabaseManager
{
    private string $serverConfigurationKey;

    public function __construct(string $serverConfigurationKey, string $dsn, ?string $username = null, ?string $password = null, ?array $options = null, bool $useDatabaseErrorLogging = false)
    {
        parent::__construct($dsn, $username, $password, $options, $useDatabaseErrorLogging);
        $this->serverConfigurationKey = $serverConfigurationKey;
    }

    public function getServerConfigurationKey()
    {
        return $this->serverConfigurationKey;
    }
}
