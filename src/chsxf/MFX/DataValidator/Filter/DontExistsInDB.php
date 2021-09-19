<?php
/**
 * Data validation "value exists in database" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */
namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DatabaseManager;
use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating if the specified value exists in a database table
 */
class DontExistsInDB extends ExistsInDB {

	/**
	 * Constructor
	 *
	 * @param string $table Database table name
	 * @param string $field Database field name
	 * @param string $message Error message (Defaults to NULL)
	 * @param string|DatabaseManager $connection Database connection name or instance (Default to DatabaseManager::DEFAULT_CONNECTION)
	 */
	public function __construct($table, $field, $message = NULL, $connection = DatabaseManager::DEFAULT_CONNECTION) {
		if (empty($message))
			$message = sprintf(dgettext('mfx', "The '%%s' field must reprensent a non-existing entry in the '%s' table (matched on the '%s' field)."), $table, $field);
		parent::__construct($table, $field, $message, $connection);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractFilter::validate()
	 *
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		$dbm = $this->getConnection();
		$nb = $dbm->getValue(sprintf("SELECT COUNT(*) FROM `%s` WHERE `%s` = ?", $this->getTable(), $this->getField()), $value);
		if ($nb === false || intval($nb) > 0) {
			if (empty($silent))
				$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}

}
