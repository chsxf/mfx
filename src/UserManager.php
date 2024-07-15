<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\Services\IAuthenticationService;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\IDatabaseService;

/**
 * User management class
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
final class UserManager implements IAuthenticationService
{
    /**
     * @var User Current registered user reference
     */
    private ?User $currentAuthenticatedUser = null;

    /**
     * Constructor
     * @since 2.0
     */
    public function __construct(private readonly IConfigService $configService, private readonly IDatabaseService $databaseService)
    {
        // Validating
        if (!empty($_SESSION['logged_user'])) {
            $newUser = $this->instantiateUser();

            list($userId, $ip) = explode('|', $_SESSION['logged_user'], 2);
            if ($ip != $_SERVER['REMOTE_ADDR'] || !$newUser->validateWithId($userId)) {
                unset($_SESSION['logged_user']);
            } else {
                $this->currentAuthenticatedUser = $newUser;
            }
        }
    }

    /**
     * Gets the current user reference
     * @since 2.0
     * @return User
     */
    public function getCurrentAuthenticatedUser(): ?User
    {
        return $this->currentAuthenticatedUser;
    }

    /**
     * Tells if an authenticated user currently exists
     * @since 2.0
     * @return bool 
     */
    public function hasAuthenticatedUser(): bool
    {
        return $this->currentAuthenticatedUser !== null;
    }

    /**
     * @return User 
     */
    private function instantiateUser(): User
    {
        $rc = new \ReflectionClass($this->configService->getValue(ConfigConstants::USER_MANAGEMENT_CLASS, __CLASS__));
        return $rc->newInstance($this, $this->databaseService);
    }

    /**
     * Validates a user session using database fields
     * @since 2.0
     * @param array $fields Key-value pairs for database validation
     * @return boolean true if the session has been validated, false either
     */
    public function validateWithFields(array $fields): bool
    {
        $this->invalidate();

        $newUser = $this->instantiateUser();
        if ($newUser->validateWithFields($fields)) {
            $this->setSessionWithUserId($this->currentAuthenticatedUser->getId());
            $this->currentAuthenticatedUser = $newUser;
            return true;
        }

        return false;
    }

    /**
     * Sets in session the current user's identifier if not already set
     * @since 2.0
     * @param string $id Current user's identifier
     */
    private function setSessionWithUserId(string $id)
    {
        if (!isset($_SESSION['logged_user'])) {
            $_SESSION['logged_user'] = sprintf("%s|%s", $id, $_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Invalidates user session.
     * Logs out the current valid user if existing
     * @since 2.0
     */
    public function invalidate()
    {
        unset($_SESSION['logged_user']);
        $this->currentAuthenticatedUser = null;
    }

    /**
     * Retrieves users management identifier field name
     * @since 2.0
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public function getIdField(): string
    {
        $idFieldName = $this->configService->getValue(ConfigConstants::USER_MANAGEMENT_ID_FIELD, 'user_id');
        if (!is_string($idFieldName)) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Users management identifier field name is not a string.");
        }
        if (!preg_match('/^[[:alnum:]_]+$/', $idFieldName)) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Users management identifier field name contains invalid characters (only underscores and alphanumeric characters are accepted).");
        }
        return $idFieldName;
    }

    /**
     * Retrieves users management table name
     * @since 2.0
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public function getTableName(): string
    {
        $tableName = $this->configService->getValue(ConfigConstants::USER_MANAGEMENT_TABLE, 'mfx_users');
        if (!is_string($tableName)) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Users management table name is not a string.");
        }
        if (!preg_match('/^[[:alnum:]_]+$/', $tableName)) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Users management table name contains invalid characters (only underscores and alphanumeric characters are accepted).");
        }
        return $tableName;
    }
}
