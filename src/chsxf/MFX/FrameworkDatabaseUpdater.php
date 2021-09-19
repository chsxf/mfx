<?php
namespace chsxf\MFX;

final class FrameworkDatabaseUpdater implements IDatabaseUpdater {
	
	/**
	 * {@inheritDoc}
	 * @see \chsxf\MFX\DatabaseUpdater::key()
	 */
	public function key() {
		return 'php-micro-framework';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \chsxf\MFX\DatabaseUpdater::pathToSQLFile()
	 */
	public function pathToSQLFile() {
		return MFX_ROOT . '/php-micro-framework.sql';
	}
	
}