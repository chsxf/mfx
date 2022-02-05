<?php
namespace chsxf\MFX;

final class FrameworkDatabaseUpdater implements IDatabaseUpdater {
	
	/**
	 * {@inheritDoc}
	 * @see \chsxf\MFX\DatabaseUpdater::key()
	 */
	public function key(): string {
		return 'mfx';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \chsxf\MFX\DatabaseUpdater::pathToSQLFile()
	 */
	public function pathToSQLFile(): string {
		$root = constant('chsxf\MFX\ROOT') ?? '';
		return "{$root}/mfx.sql";
	}
	
}