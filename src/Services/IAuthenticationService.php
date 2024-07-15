<?php

namespace chsxf\MFX\Services;

use chsxf\MFX\User;

/**
 * @since 2.0
 */
interface IAuthenticationService
{
    /**
     * Gets the current user reference
     * @since 2.0
     * @return User
     */
    function getCurrentAuthenticatedUser();

    /**
     * Tells if an authenticated user currently exists
     * @since 2.0
     * @return bool 
     */
    function hasAuthenticatedUser(): bool;

    /**
     * Validates a user session using database fields
     * @since 2.0
     * @param array $fields Key-value pairs for database validation
     * @return boolean true if the session has been validated, false either
     */
    function validateWithFields(array $fields): bool;

    /**
     * Invalidates user session.
     * Logs out the current valid user if existing
     * @since 2.0
     */
    function invalidate();

    /**
     * Retrieves users management identifier field name
     * @since 2.0
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    function getIdField(): string;

    /**
     * Retrieves users management table name
     * @since 2.0
     * @throws MFXException If the provided value is not a string or contains invalid characters (only underscores and alphanumeric characters are accepted)
     * @return string
     */
    function getTableName(): string;
}
