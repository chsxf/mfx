<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\Attributes\AnonymousRoute;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\Routers\BaseRouteProvider;

/**
 * @since 1.0
 */
final class DatabaseUpdater extends BaseRouteProvider
{
    private ?array $updatersData = null;
    private ?string $updatersDomain = null;

    public const DEFAULT_DOMAIN = 'application';

    /**
     * @return RequestResult|false
     */
    #[Route]
    #[AnonymousRoute]
    public function update(): RequestResult|false
    {
        $updaters = array_merge(array(FrameworkDatabaseUpdater::class), $this->serviceProvider->getConfigService()->getValue(ConfigConstants::DATABASE_UPDATERS_CLASSES, array()));
        if (!is_array($updaters) || empty($updaters)) {
            return false;
        }

        // Retrieving updaters domain
        $this->updatersDomain = $this->serviceProvider->getConfigService()->getValue(ConfigConstants::DATABASE_UPDATERS_DOMAIN, self::DEFAULT_DOMAIN);
        if (!preg_match('/^[[:alnum:]_-]+$/', $this->updatersDomain)) {
            $this->updatersDomain = self::DEFAULT_DOMAIN;
        }

        // Initializing database manager
        $dbConn = $this->serviceProvider->getDatabaseService()->open('__mfx');

        // Creating updaters table
        $dbConn->exec("CREATE TABLE IF NOT EXISTS `mfx_database_updaters` (
					`updater_key` varchar(255) COLLATE utf8_bin NOT NULL,
					`updater_domain` varchar(255) COLLATE utf8_bin NOT NULL,
					`updater_version` smallint(5) unsigned NOT NULL,
					`updater_file_modified` TIMESTAMP NULL DEFAULT NULL,
					PRIMARY KEY (`updater_key` (10), `updater_domain` (10))
		)");

        // Load versions and file modification times
        $sql = "SELECT `updater_key`, `updater_version`, UNIX_TIMESTAMP(`updater_file_modified`) AS `updater_filemtime`
					FROM `mfx_database_updaters`
                    WHERE `updater_domain` = ?";
        $fetchedUpdatersData = $dbConn->getIndexed($sql, 'updater_key', \PDO::FETCH_OBJ, $this->updatersDomain);
        $this->updatersData = is_array($fetchedUpdatersData) ? $fetchedUpdatersData : [];

        foreach ($updaters as $updater) {
            $rc = new \ReflectionClass($updater);
            if (!$rc->implementsInterface(IDatabaseUpdater::class)) {
                continue;
            }
            if ($this->ensureUpToDate($rc->newInstance(), $dbConn) === false) {
                break;
            }
        }

        return RequestResult::buildStatusRequestResult(HttpStatusCodes::ok);
    }

    private function ensureUpToDate(IDatabaseUpdater $updater, DatabaseConnectionInstance $dbConn): bool
    {
        $key = $updater->key();
        $pathToSQL = $updater->pathToSQLFile();

        // Looking for SQL file
        if (empty($pathToSQL) || !is_string($pathToSQL) || !file_exists($pathToSQL) || !is_file($pathToSQL) || !is_readable($pathToSQL)) {
            trigger_error(sprintf(dgettext('mfx', "Wrong SQL update file path for DatabaseUpdater '%s'."), $key), E_USER_ERROR);
            return false;
        }

        // Checking file modification time
        $mtime = filemtime($pathToSQL);
        if (array_key_exists($key, $this->updatersData) && $this->updatersData[$key]->updater_filemtime == $mtime) {
            return true;
        }

        // Fetching SQL content
        $fc = file_get_contents($pathToSQL);
        $chunks = preg_split('/^-- \[\s*(VERSION:\s*\d+)\s*\](?:\s+\[\s*(CONNECTION:\s*\S+)\s*\])?$/mU', $fc, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        // Updating
        $regs = null;
        $version = null;
        $dbm = null;
        foreach ($chunks as $chunk) {
            if ($version === null && !preg_match('/^VERSION:\s*(\d+)$/', $chunk)) {
                trigger_error(sprintf(dgettext('mfx', "The SQL update file should start with a VERSION token (updater key: '%s')."), $key), E_USER_ERROR);
                return false;
            }

            if (preg_match('/^VERSION:\s*(\d+)$/', $chunk, $regs)) {
                $version = intval($regs[1]);
            } elseif (preg_match('/^CONNECTION:\s*(\S+)$/', $chunk, $regs)) {
                $dbm = $this->serviceProvider->getDatabaseService()->open($regs[1]);
            } else {
                if (array_key_exists($key, $this->updatersData) && $this->updatersData[$key]->updater_version >= $version) {
                    continue;
                }

                $chunk = str_replace(
                    array('__MFX_USER_ID_FIELD_NAME__', '__MFX_USERS_TABLE_NAME__'),
                    array(
                        $this->serviceProvider->getConfigService()->getValue(ConfigConstants::USER_MANAGEMENT_ID_FIELD, 'user_id'),
                        $this->serviceProvider->getConfigService()->getValue(ConfigConstants::USER_MANAGEMENT_TABLE, 'mfx_users')
                    ),
                    $chunk
                );

                $queries = preg_split('/;$/m', $chunk);
                $queries = array_map('trim', $queries);
                if ($dbm === null) {
                    $dbm = $this->serviceProvider->getDatabaseService()->open();
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
                if ($dbConn->exec($sql, $key, $this->updatersDomain, $version, $mtime) === false) {
                    trigger_error(sprintf(dgettext('mfx', "An error has occured while processing DatabaseUpdater '%s'."), $key), E_USER_ERROR);
                    return false;
                }
            }
        }

        return true;
    }
}
