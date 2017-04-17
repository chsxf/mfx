<?php
namespace CheeseBurgames\MFX;

final class FrameworkDatabaseUpdater extends DatabaseUpdater {
	
	/**
	 * {@inheritDoc}
	 * @see \CheeseBurgames\MFX\DatabaseUpdater::key()
	 */
	protected function key() {
		return 'php-micro-framework';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \CheeseBurgames\MFX\DatabaseUpdater::pathToSQLFile()
	 */
	protected function pathToSQLFile() {
		return MFX_ROOT . '/php-micro-framework.sql';
	}
	
}