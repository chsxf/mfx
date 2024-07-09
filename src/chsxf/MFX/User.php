<?php

/**
 * User descriptor
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * User description class
 * @since 1.0
 */
class User
{
    /**
     * @var User Current registered user reference
     */
    private static ?User $_currentUser = null;

    /**
     * @var boolean If set, the current registered is a valid user. Either, the user is a guest
     */
    private bool $_valid;

    /**
     * @var string User key. NULL for guests and most commonly the user database ID for valid users.
     */
    private ?string $_key;

    /**
     * @var array User data fetched from the database
     */
    private ?array $_data;

    /**
     * @var boolean If set, user data has been fetched.
     */
    private bool $_dataFetched;

    /**
     * Validates user session
     * @since 1.0
     */
    public static function validate()
    {
        // Authenticator class
        $rc = new \ReflectionClass(Config::get(ConfigConstants::USER_MANAGEMENT_CLASS, __CLASS__));
        self::$_currentUser = $rc->newInstance();

        // Validating
        if (!empty($_SESSION['logged_user'])) {
            list($key, $ip) = explode('|', $_SESSION['logged_user'], 2);

            if ($ip != $_SERVER['REMOTE_ADDR'] || !self::$_currentUser->registerFromKey($key)) {
                unset($_SESSION['logged_user']);
            }
        }
    }

    /**
     * Validates a user session using database fields
     * @since 1.0
     * @param array $fields Key-value pairs for database validation
     * @return boolean true if the session has been validated, false either
     */
    public static function validateWithFields(array $fields): bool
    {
        if (!self::$_currentUser->isValid() && self::$_currentUser->registerWithFields($fields)) {
            self::setSessionWithUserKey(self::$_currentUser->getKey());
            return true;
        }
        return false;
    }

    /**
     * Sets in session the current user's key if not already set
     * @since 1.0
     * @param string $key Current user's key
     */
    protected static function setSessionWithUserKey(string $key)
    {
        if (!isset($_SESSION['logged_user'])) {
            $_SESSION['logged_user'] = sprintf("%s|%s", $key, $_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Invalidates user session.
     * Logs out the current valid user if existing
     * @since 1.0
     */
    public static function invalidate()
    {
        unset($_SESSION['logged_user']);
    }

    /**
     * Gets the current user reference
     * @since 1.0
     * @return User
     */
    public static function currentUser(): ?User
    {
        return self::$_currentUser;
    }

    /**
     * Constructor
     * @since 1.0
     * @param string $key User key
     */
    public function __construct(string $key = null)
    {
        $this->_valid = false;
        $this->_key = null;

        $this->_data = null;
        $this->_dataFetched = false;

        if ($key !== null) {
            $this->registerFromKey($key);
        }
    }

    /**
     * Retrieves users management key field name
     * @since 1.0
     * @throws \InvalidArgumentException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public static function getKeyField(): string
    {
        $keyFieldName = Config::get(ConfigConstants::USER_MANAGEMENT_KEY_FIELD, 'user_id');
        if (!is_string($keyFieldName)) {
            throw new \InvalidArgumentException("Users management key field name is not a string.");
        }
        if (!preg_match('/^[[:alnum:]_]+$/', $keyFieldName)) {
            throw new \InvalidArgumentException("Users management key field name contains invalid characters (only underscores and alphanumeric characters are accepted).");
        }
        return $keyFieldName;
    }

    /**
     * Retrieves users management table name
     * @since 1.0
     * @throws \InvalidArgumentException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public static function getTableName(): string
    {
        $tableName = Config::get(ConfigConstants::USER_MANAGEMENT_TABLE, 'mfx_users');
        if (!is_string($tableName)) {
            throw new \InvalidArgumentException("Users management table name is not a string.");
        }
        if (!preg_match('/^[[:alnum:]_]+$/', $tableName)) {
            throw new \InvalidArgumentException("Users management table name contains invalid characters (only underscores and alphanumeric characters are accepted).");
        }
        return $tableName;
    }

    /**
     * Register a user from database fields
     * @since 1.0
     * @param array $fields Database fields used to identify the user
     * @return boolean true if the user is valid, false either
     */
    public function registerWithFields(array $fields): bool
    {
        if (empty($fields)) {
            return false;
        }

        $sql = sprintf("SELECT `%s` FROM `%s` WHERE ", self::getKeyField(), self::getTableName());
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

        $dbm = DatabaseManager::open("__mfx");
        $key = call_user_func_array(array(
            &$dbm,
            'getValue'
        ), $values);
        $dbm = null;
        if ($key === false) {
            return false;
        }

        $this->_key = $key;
        $this->_valid = $this->validateUser();
        return true;
    }

    /**
     * Validates the user key
     * @since 1.0
     * @param string $key User key to validate
     * @return boolean true if the user key is valid, false either
     * @used-by User::registerFromKey()
     */
    protected function validateKey(string $key): bool
    {
        $dbm = DatabaseManager::open("__mfx");
        $nb = $dbm->getValue(sprintf('SELECT COUNT(`%1$s`) FROM `%2$s` WHERE `%1$s` = ?', self::getKeyField(), self::getTableName()), $key);
        return !empty($nb);
    }

    /**
     * Validates the user
     * @since 1.0
     * @return boolean true if the user is valid, false either
     */
    protected function validateUser(): bool
    {
        return $this->_key !== null;
    }

    /**
     * Register a user from its key
     * @since 1.0
     * @param string $key User key
     * @return boolean true is the key is valid, false either
     * @uses User::_validateKey()
     */
    public function registerFromKey($key): bool
    {
        if ($this->validateKey($key)) {
            $this->_key = $key;
            $this->_valid = $this->validateUser();
            return true;
        }
        return false;
    }

    /**
     * Gets the current user key
     * @since 1.0
     * @return string The function returns NULL if no valid user is currently registered
     */
    public function getKey(): string
    {
        return $this->_key;
    }

    /**
     * Gets the current user status.
     * @since 1.0
     * @return boolean true if the current user is valid, false for guests
     */
    public function isValid(): bool
    {
        return $this->_valid;
    }

    /**
     * Fetches user data from the database
     * @since 1.0
     * @return boolean true if data has been successfully fetched, false either
     */
    final protected function fetch(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->_dataFetched == false) {
            if (($data = $this->fetchData()) === false) {
                $this->_valid = false;
                return false;
            }

            $this->_data = $data;
            $this->_dataFetched = true;
        }

        return true;
    }

    /**
     * Fetches user data from the database.
     * This function can be overridden.
     * @since 1.0
     * @return mixed An associative array if data could be fetched, false either.
     */
    protected function fetchData(): mixed
    {
        $dbm = DatabaseManager::open('__mfx');
        $row = $dbm->getRow($this->getFetchDataQuery(), \PDO::FETCH_ASSOC, $this->_key);
        $dbm = null;
        return $row;
    }

    /**
     * Builds the user data's fetch query
     * @since 1.0
     * @return string
     */
    protected function getFetchDataQuery(): string
    {
        return sprintf("SELECT * FROM `%s` WHERE `%s` = ?", self::getTableName(), self::getKeyField());
    }

    /**
     * Tells if data has been fetched and is ready to use
     * @since 1.0
     * @return boolean true if data is ready to use, false either.
     */
    final protected function isDataReady(): bool
    {
        return $this->isValid() && ($this->_dataFetched || $this->fetch());
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
        return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
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
        return $this->isDataReady() && isset($this->_data[$name]);
    }
}
