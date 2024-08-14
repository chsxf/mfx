<?php

declare(strict_types=1);

/**
 * Data validation "value exists in database" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DatabaseConnectionInstance;
use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating if the specified value exists in a database table
 * @since 1.0
 */
class ExistsInDB extends AbstractFilter
{
    /**
     * @var DatabaseConnectionInstance Database connection instance
     */
    private DatabaseConnectionInstance $connection;

    /**
     * @var string Database table name
     */
    private string $table;

    /**
     * @var string Database field name
     */
    private string $field;

    /**
     * Constructor
     * @since 1.0
     * @param string $table Database table name
     * @param string $field Database field name
     * @param string $message Error message (Defaults to NULL)
     * @param DatabaseConnectionInstance $connection Database connection instance
     */
    public function __construct(string $table, string $field, ?string $message = null, DatabaseConnectionInstance $connection)
    {
        if (empty($message)) {
            $message = sprintf(dgettext('mfx', "The '%%s' field must reprensent an existing entry in the '%s' table (matched on the '%s' field)."), $table, $field);
        }
        parent::__construct($message);

        $this->connection = $connection;
        $this->table = preg_replace('/[^a-z0-9_]/', '', $table);
        $this->field = preg_replace('/[^a-z0-9_]/', '', $field);
    }

    /**
     * (non-PHPdoc)
     *
     * @ignore
     * @see AbstractFilter::validate()
     *
     * @param string $fieldName Field's name
     * @param mixed $value Field's value
     * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
     * @param boolean $silent If set, no error is triggered (defaults to false)
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
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
     * Gets the database connection instance
     * @since 1.0
     * @return DatabaseConnectionInstance
     */
    final protected function getConnection(): DatabaseConnectionInstance
    {
        return $this->connection;
    }

    /**
     * Gets the table name
     * @since 1.0
     * @return string
     */
    final protected function getTable(): string
    {
        return $this->table;
    }

    /**
     * Gets the field name
     * @since 1.0
     * @return string
     */
    final protected function getField(): string
    {
        return $this->field;
    }

    /**
     * Gets the SQL query
     * @since 1.0
     * @return string
     */
    protected function getSQLQuery(): string
    {
        return sprintf("SELECT COUNT(*) FROM `%s` WHERE `%s` = ?", $this->getTable(), $this->getField());
    }

    /**
     * Gets the SQL query values
     * @since 1.0
     * @param mixed $value Field's value
     * @return array
     */
    protected function getSQLValues(mixed $value): array
    {
        return array($value);
    }
}
