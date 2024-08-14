<?php

declare(strict_types=1);

/**
 * Data validation "value exists in database" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DatabaseConnectionInstance;

/**
 * Description of a filter validating if the specified value exists in a database table
 * @since 1.0
 */
class DoNotExistInDB extends ExistsInDB
{
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
            $message = sprintf(dgettext('mfx', "The '%%s' field must reprensent a non-existing entry in the '%s' table (matched on the '%s' field)."), $table, $field);
        }
        parent::__construct($table, $field, $message, $connection);
    }

    /**
     * (non-PHPdoc)
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
        $dbConn = $this->getConnection();
        $nb = $dbConn->getValue(sprintf("SELECT COUNT(*) FROM `%s` WHERE `%s` = ?", $this->getTable(), $this->getField()), $value);
        if ($nb === false || intval($nb) > 0) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
