<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\Services\IAuthenticationService;
use chsxf\MFX\Services\IDatabaseService;

/**
 * User description class
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
class User
{
    /**
     * @var boolean If set, the current registered is a valid user. Either, the user is a guest
     */
    private bool $valid;

    /**
     * @var string User identifier. NULL for guests and most commonly the user database ID for valid users.
     */
    private ?string $id;

    /**
     * @var array User data fetched from the database
     */
    private ?array $data;

    /**
     * @var boolean If set, user data has been fetched.
     */
    private bool $dataFetched;

    protected readonly IAuthenticationService $authenticationService;
    protected readonly IDatabaseService $databaseService;

    /**
     * Constructor
     * @since 2.0
     * @param IAuthenticationService $authenticationService Authentication service instance
     * @param IDatabaseService $databaseService Database service instance
     */
    public function __construct(IAuthenticationService $authenticationService, IDatabaseService $databaseService)
    {
        $this->authenticationService = $authenticationService;
        $this->databaseService = $databaseService;

        $this->valid = false;
        $this->id = null;

        $this->data = null;
        $this->dataFetched = false;
    }

    /**
     * Validates the user from its identifier
     * @since 2.0
     * @param string $id User identifier to validate
     * @return boolean true if the user identifier is valid, false either
     */
    public function validateWithId(string $id): bool
    {
        if ($this->valid && $this->id === $id) {
            return true;
        }

        $dbConn = $this->databaseService->open("__mfx");
        $nb = $dbConn->getValue(sprintf('SELECT COUNT(`%1$s`) FROM `%2$s` WHERE `%1$s` = ?', $this->authenticationService->getIdField(), $this->authenticationService->getTableName()), $id);
        if (empty($nb)) {
            return false;
        }

        $this->id = $id;
        return ($this->valid = $this->validate());
    }

    /**
     * Validates a user from database fields
     * @since 2.0
     * @param array $fields Database fields used to identify the user
     * @return boolean true if the user is valid, false either
     */
    public function validateWithFields(array $fields): bool
    {
        if (empty($fields)) {
            return false;
        }

        $sql = sprintf("SELECT `%s` FROM `%s` WHERE ", $this->authenticationService->getIdField(), $this->authenticationService->getTableName());
        $validFields = array();
        $values = array();
        foreach ($fields as $f) {
            if (!array_key_exists('value', $f) || !preg_match('/^\w+$/', $f['name']) || (!empty($f['function']) && !preg_match("/^[a-zA-Z0-9_\-?(),'` ]+$/", $f['function'])) || (!empty($f['operator']) && !in_array($f['operator'], array(
                '=',
                '!=',
                '<>',
                '<=',
                '>=',
                'IS',
                'IS NOT'
            )))) {
                return false;
            }

            $str = "`{$f['name']}`";
            if ($f['value'] === null) {
                $str .= ' IS ';
            } else {
                $str .= empty($f['operator']) ? ' = ' : $f['operator'];
            }
            if (!empty($f['function'])) {
                if (strpos($f['function'], '(') === false) {
                    $str .= "{$f['function']}(?)";
                } else {
                    $str .= $f['function'];
                }
            } else {
                $str .= '?';
            }
            $validFields[] = $str;

            $values[] = $f['value'];
        }
        $sql .= implode(' AND ', $validFields) . ' LIMIT 1';
        array_unshift($values, $sql);

        $dbConn = $this->databaseService->open("__mfx");
        $id = call_user_func_array($dbConn->getValue(...), $values);
        if ($id === false) {
            return false;
        }

        $this->id = $id;
        $this->valid = $this->validate();
        return true;
    }

    /**
     * Validates the user
     * @since 2.0
     * @return boolean true if the user is valid, false either
     */
    protected function validate(): bool
    {
        return $this->id !== null;
    }

    /**
     * Gets the current user identifier
     * @since 2.0
     * @return string The function returns NULL if no valid user is currently registered
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets the current user status.
     * @return boolean true if the current user is valid, false for guests
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Fetches user data from the database
     * @return boolean true if data has been successfully fetched, false either
     */
    final protected function fetch(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->dataFetched == false) {
            if (($data = $this->fetchData()) === false) {
                $this->valid = false;
                return false;
            }

            $this->data = $data;
            $this->dataFetched = true;
        }

        return true;
    }

    /**
     * Fetches user data from the database.
     * This function can be overridden.
     * @return mixed An associative array if data could be fetched, false either.
     */
    protected function fetchData(): mixed
    {
        $dbConn = $this->databaseService->open('__mfx');
        $row = $dbConn->getRow($this->getFetchDataQuery(), \PDO::FETCH_ASSOC, $this->id);
        return $row;
    }

    /**
     * Builds the user data's fetch query
     * @return string
     */
    protected function getFetchDataQuery(): string
    {
        return sprintf("SELECT * FROM `%s` WHERE `%s` = ?", $this->authenticationService->getTableName(), $this->authenticationService->getIdField());
    }

    /**
     * Tells if data has been fetched and is ready to use
     * @return boolean true if data is ready to use, false either.
     */
    final protected function isDataReady(): bool
    {
        return $this->isValid() && ($this->dataFetched || $this->fetch());
    }

    /**
     * PHP magic method
     *
     * @ignore
     *
     * @param string $name Variable name
     * @return mixed
     *
     * @link http://www.php.net/manual/en/language.oop5.magic.php
     */
    public function __get(string $name): mixed
    {
        if (!$this->isDataReady()) {
            return null;
        }
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * PHP magic method
     *
     * @ignore
     *
     * @param string $name Varible name
     * @return boolean
     *
     * @link http://www.php.net/manual/en/language.oop5.magic.php
     */
    public function __isset(string $name): bool
    {
        return $this->isDataReady() && isset($this->data[$name]);
    }
}
