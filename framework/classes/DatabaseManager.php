<?php

/**
 * Database management helpers
 *
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */
namespace CheeseBurgames\MFX;

use CheeseBurgames\PDO\DatabaseManagerException;

/**
 * Database manager class
 */
final class DatabaseManager extends \CheeseBurgames\PDO\DatabaseManager {
	const DEFAULT_CONNECTION = '__default';

	/**
	 * @var array Open connections container
	 */
	private static $_openConnections = array();

	private $_serverConfigurationKey;

	/**
	 * Constructor
	 *
	 * @param string $dsn Data Source Name (ie mysql:host=localhost;dbname=mydb)
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $server Server configuration key
	 * @see \PDO::__construct()
	 */
	public function __construct($dsn, $username, $password, $server = self::DEFAULT_CONNECTION) {
		parent::__construct($dsn, $username, $password, Config::get('database.error_logging', false));

		$this->_serverConfigurationKey = $server;
	}

	/**
	 * Opens a connection to a database server, or returns the currently active connection to this server
	 *
	 * @param string $server Server configuration key (Defaults to __default).
	 * @param bool $forceNew If set, a new connection is open even if a previous similar one exists in the cache (Defaults to false)
	 * @throws DatabaseManagerException If no configuration is available nor valid for this server key
	 * @return DatabaseManager
	 */
	public static function open($server = self::DEFAULT_CONNECTION, $forceNew = false) {
		if (array_key_exists($server, self::$_openConnections) && empty($forceNew))
			return self::$_openConnections[$server];

		if (!Config::has('database.servers'))
			throw new DatabaseManagerException("No database server configured.");

		$serverConfig = Config::get("database.servers.{$server}");
		if (is_string($serverConfig) && !empty($serverConfig))
			$serverConfig = Config::get("database.servers.{$serverConfig}");
		if (empty($serverConfig) || !is_array($serverConfig))
			throw new DatabaseManagerException("No server can be found for the '{$server}' key.");

		$dsn = NULL;
		$username = NULL;
		$password = NULL;
		foreach (array( 
				'dsn',
				'username',
				'password'
		) as $p) {
			if (empty($serverConfig[$p]))
				throw new DatabaseManagerException("Unable to find the '{$p}' parameter for database server '{$server}'.");
			$$p = $serverConfig[$p];
		}

		$dbm = new DatabaseManager($dsn, $username, $password, $server);
		if (!array_key_exists($server, self::$_openConnections))
			self::$_openConnections[$server] = $dbm;
		return $dbm;
	}

	public static function close(DatabaseManager &$_manager) {
		unset(self::$_openConnections[$_manager->_serverConfigurationKey]);
		$_manager = NULL;
	}

}
