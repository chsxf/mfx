<?php
/**
 * Data validation "value exists in database" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;
use chsxf\MFX\DatabaseManager;

/**
 * Description of a filter validating if the specified value exists in a database table
 */
class ExistsInDB extends AbstractFilter {

	/**
	 * @var string|DatabaseManager Database connection name or instance
	 */
	private string|DatabaseManager $_connection;

	/**
	 * @var string Database table name
	 */
	private string $_table;

	/**
	 * @var string Database field name
	 */
	private string $_field;

	/**
	 * Constructor
	 *
	 * @param string $table Database table name
	 * @param string $field Database field name
	 * @param string $message Error message (Defaults to NULL)
	 * @param string|DatabaseManager $connection Database connection name or instance (Default to DatabaseManager::DEFAULT_CONNECTION)
	 */
	public function __construct(string $table, string $field, ?string $message = NULL, string|DatabaseManager $connection = DatabaseManager::DEFAULT_CONNECTION) {
        if (empty($message)) {
            $message = sprintf(dgettext('mfx', "The '%%s' field must reprensent an existing entry in the '%s' table (matched on the '%s' field)."), $table, $field);
        }
		parent::__construct($message);

		$this->_connection = $connection;
		$this->_table = preg_replace('/[^a-z0-9_]/', '', $table);
		$this->_field = preg_replace('/[^a-z0-9_]/', '', $field);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractFilter::validate()
	 *
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool {
		$dbm = $this->getConnection();
		$nb = $dbm->getValue($this->getSQLQuery(), $this->getSQLValues($value));
		if (intval($nb) === 0) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
			return false;
		}
		return true;
	}

	/**
	 * Gets a database manager instance based on provided connection name or instance
	 *
	 * @return DatabaseManager
	 */
	protected final function getConnection(): DatabaseManager {
        if ($this->_connection instanceof DatabaseManager) {
            return $this->_connection;
        }
		return DatabaseManager::open($this->_connection);
	}

	/**
	 * Gets the table name
	 *
	 * @return string
	 */
	protected final function getTable(): string {
		return $this->_table;
	}

	/**
	 * Gets the field name
	 *
	 * @return string
	 */
	protected final function getField(): string {
		return $this->_field;
	}

	/**
	 * Gets the SQL query
	 *
	 * @return string
	 */
	protected function getSQLQuery(): string {
		return sprintf("SELECT COUNT(*) FROM `%s` WHERE `%s` = ?", $this->getTable(), $this->getField());
	}

	/**
	 * Gets the SQL query values
	 *
	 * @param mixed $_value Field's value
	 * @return array
	 */
	protected function getSQLValues(mixed $_value): array {
		return array($_value);
	}

}