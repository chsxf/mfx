<?php
namespace CheeseBurgames\MFX;

interface IDatabaseUpdater {
	/**
	 * Retrieves the key for this updater
	 * @return string
	 */
	public function key();
	
	/**
	 * Retrieves the path to the SQL update file for this updater
	 * @return string
	 */
	public function pathToSQLFile();
}