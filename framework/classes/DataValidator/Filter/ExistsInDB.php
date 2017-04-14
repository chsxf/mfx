<?php
/**
 * Data validation "value exists in database" filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filters;

use CheeseBurgames\MFX\DataValidator\AbstractFilter;
use CheeseBurgames\MFX\DatabaseManager;

/**
 * Description of a filter validating if the specified value exists in a database table
 */
class ExistsInDB extends AbstractFilter
{
	/**
	 * @var string|DatabaseManager Database connection name or instance
	 */
	private $_connection;
	/**
	 * @var string Database table name
	 */
	private $_table;
	/**
	 * @var string Database field name
	 */
	private $_field;
	
	/**
	 * Constructor
	 * @param string $table Database table name
	 * @param string $field Database field name
	 * @param string $message Error message (Defaults to NULL)
	 * @param string|DatabaseManager $connection Database connection name or instance (Default to '__default')
	 */
	public function __construct($table, $field, $message = NULL, $connection = '__default')
	{
		if (empty($message))
			$message = sprintf(dgettext('mfx', "The '%%s' field must reprensent an existing entry in the '%s' table (matched on the '%s' field)."), $table, $field);
		parent::__construct($message);
		
		$this->_connection = $connection;
		$this->_table = preg_replace('/[^a-z0-9_]/', '', $table);
		$this->_field = preg_replace('/[^a-z0-9_]/', '', $field);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 */
	public function validate($fieldName, $value, $atIndex = NULL)
	{
		$dbm = $this->getConnection();
		$nb = $dbm->getValue(sprintf("SELECT COUNT(*) FROM `%s` WHERE `%s` = ?", $this->getTable(), $this->getField()), $value);
		if (intval($nb) != 1)
		{
			$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
	
	/**
	 * Gets a database manager instance based on provided connection name or instance
	 * @return DatabaseManager
	 */
	protected final function getConnection() {
		if ($this->_connection instanceof DatabaseManager)
			return $this->_connection;
		else
			return DatabaseManager::open($this->_connection);
	}
	
	/**
	 * Gets the table name
	 * @return string
	 */
	protected final function getTable() {
		return $this->_table;
	}
	
	/**
	 * Gets the field name
	 * @return string
	 */
	protected final function getField() {
		return $this->_field;
	}
}