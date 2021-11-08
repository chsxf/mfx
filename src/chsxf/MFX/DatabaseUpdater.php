<?php
namespace chsxf\MFX;

use chsxf\MFX\Attributes\AnonymousAttribute;
use chsxf\MFX\Attributes\SubRouteAttribute;

final class DatabaseUpdater implements IRouteProvider {

	private static ?array $_updatersData = NULL;
	private static ?string $_updatersDomain = NULL;

	#[SubRouteAttribute]
	#[AnonymousAttribute]
	public static function update(): RequestResult|false {
		$updaters = array_merge(array( FrameworkDatabaseUpdater::class ), Config::get('database.updaters.classes', array()));
        if (!is_array($updaters) || empty($updaters)) {
            return false;
        }

		// Retrieving updaters domain
		self::$_updatersDomain = Config::get('database.updaters.domain', NULL);
        if (!preg_match('/^[[:alnum:]_-]+$/', self::$_updatersDomain)) {
            self::$_updatersDomain = null;
        }

		// Initializing database manager
		$dbm = DatabaseManager::open('__mfx');

		// Creating updaters table
		$dbm->exec("CREATE TABLE IF NOT EXISTS `mfx_database_updaters` (
					`updater_key` varchar(255) COLLATE utf8_bin NOT NULL,
					`updater_domain` varchar(255) COLLATE utf8_bin NOT NULL,
					`updater_version` smallint(5) unsigned NOT NULL,
					`updater_file_modified` TIMESTAMP NULL DEFAULT NULL,
					PRIMARY KEY (`updater_key` (10), `updater_domain` (10))
		)");

		// Load versions and file modification times
		$sql = "SELECT `updater_key`, `updater_version`, UNIX_TIMESTAMP(`updater_file_modified`) AS `updater_filemtime`
					FROM `mfx_database_updaters`";
        if (self::$_updatersDomain === null) {
            $sql .= " WHERE `updater_domain` IS NULL";
        }
		else {
            $sql .= " WHERE `updater_domain` = ?";
        }
		self::$_updatersData = $dbm->getIndexed($sql, 'updater_key', \PDO::FETCH_OBJ, self::$_updatersDomain);

		foreach ($updaters as $updater) {
			$rc = new \ReflectionClass($updater);
            if (!$rc->implementsInterface(IDatabaseUpdater::class)) {
                continue;
            }
            if (self::ensureUpToDate($rc->newInstance(), $dbm) === false) {
                break;
            }
		}

		return RequestResult::buildStatusRequestResult(200);
	}

	private static function ensureUpToDate(IDatabaseUpdater $updater, DatabaseManager $dbmMFX): bool {
		$key = $updater->key();
		$pathToSQL = $updater->pathToSQLFile();

		// Looking for SQL file
		if (empty($pathToSQL) || !is_string($pathToSQL) || !file_exists($pathToSQL) || !is_file($pathToSQL) || !is_readable($pathToSQL)) {
			trigger_error(sprintf(dgettext('mfx', "Wrong SQL update file path for DatabaseUpdater '%s'."), $key), E_USER_ERROR);
			return false;
		}

		// Checking file modification time
		$mtime = filemtime($pathToSQL);
        if (array_key_exists($key, self::$_updatersData) && self::$_updatersData[$key]->updater_filemtime == $mtime) {
            return true;
        }

		// Fetching SQL content
		$fc = file_get_contents($pathToSQL);
		$chunks = preg_split('/^-- \[\s*(VERSION:\s*\d+)\s*\](?:\s+\[\s*(CONNECTION:\s*\S+)\s*\])?$/mU', $fc, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		// Updating
		$regs = NULL;
		$version = NULL;
		$dbm = NULL;
		foreach ($chunks as $chunk) {
			if ($version === NULL && !preg_match('/^VERSION:\s*(\d+)$/', $chunk)) {
				trigger_error(sprintf(dgettext('mfx', "The SQL update file should start with a VERSION token (updater key: '%s')."), $key), E_USER_ERROR);
				return false;
			}

            if (preg_match('/^VERSION:\s*(\d+)$/', $chunk, $regs)) {
                $version = intval($regs[1]);
            }
			else if (preg_match('/^CONNECTION:\s*(\S+)$/', $chunk, $regs)) {
				$dbm = DatabaseManager::open($regs[1]);
			}
			else {
                if (array_key_exists($key, self::$_updatersData) && self::$_updatersData[$key]->updater_version >= $version) {
                    continue;
                }

				$chunk = str_replace(
						array( '__MFX_USER_ID_FIELD_NAME__', '__MFX_USERS_TABLE_NAME__' ),
						array( Config::get('user_management.key_field', 'user_id'), Config::get('user_management.table', 'mfx_users') ),
						$chunk
				);

				$queries = preg_split('/;$/m', $chunk);
				$queries = array_map('trim', $queries);
                if ($dbm === null) {
                    $dbm = DatabaseManager::open();
                }
				foreach ($queries as $query) {
					if (!empty($query) && $dbm->exec($query) === false) {
						trigger_error(sprintf(dgettext('mfx', "An error has occured while processing DatabaseUpdater '%s'."), $key), E_USER_ERROR);
						return false;
					}
				}

				$sql = "INSERT INTO `mfx_database_updaters` (`updater_key`, `updater_domain`, `updater_version`, `updater_file_modified`)
							VALUE (?, ?, ?, FROM_UNIXTIME(?))
							ON DUPLICATE KEY UPDATE `updater_version` = VALUES(`updater_version`), `updater_file_modified` = VALUES(`updater_file_modified`)";
				if ($dbmMFX->exec($sql, $key, self::$_updatersDomain, $version, $mtime) === false) {
					trigger_error(sprintf(dgettext('mfx', "An error has occured while processing DatabaseUpdater '%s'."), $key), E_USER_ERROR);
					return false;
				}
			}
		}

		return true;
	}

}