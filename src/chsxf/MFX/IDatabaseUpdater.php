<?php

namespace chsxf\MFX;

interface IDatabaseUpdater
{
	/**
	 * Retrieves the key for this updater
	 * @return string
	 */
	public function key(): string;

	/**
	 * Retrieves the path to the SQL update file for this updater
	 * @return string
	 */
	public function pathToSQLFile(): string;
}
