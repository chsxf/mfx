<?php
namespace CheeseBurgames\MFX;

final class FrameworkDatabaseUpdater implements IDatabaseUpdater {
	
	/**
	 * {@inheritDoc}
	 * @see \CheeseBurgames\MFX\DatabaseUpdater::key()
	 */
	public function key() {
		return 'php-micro-framework';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \CheeseBurgames\MFX\DatabaseUpdater::pathToSQLFile()
	 */
	public function pathToSQLFile() {
		return MFX_ROOT . '/php-micro-framework.sql';
	}
	
}