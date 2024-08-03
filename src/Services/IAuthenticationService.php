<?php

namespace chsxf\MFX\Services;

use chsxf\MFX\User;

/**
 * @since 2.0
 */
interface IAuthenticationService
{
    /**
     * Tells if user management is enabled
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Gets the current user reference
     * @return User
     */
    public function getCurrentAuthenticatedUser(): ?User;

    /**
     * Tells if an authenticated user currently exists
     * @return bool
     */
    public function hasAuthenticatedUser(): bool;

    /**
     * Validates a user session using database fields
     * @param array $fields Key-value pairs for database validation
     * @return boolean true if the session has been validated, false either
     */
    public function validateWithFields(array $fields): bool;

    /**
     * Invalidates user session.
     * Logs out the current valid user if existing
     */
    public function invalidate();

    /**
     * Retrieves users management identifier field name
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public function getIdField(): string;

    /**
     * Retrieves users management table name
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    public function getTableName(): string;
}
