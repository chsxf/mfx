<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IAuthenticationService;
use chsxf\MFX\User;

/**
 * @since 2.0
 * @ignore
 */
final class AuthenticationServiceProxy implements IAuthenticationService
{
    public function __construct(private readonly IAuthenticationService $authenticationService)
    {
    }

    public function isEnabled(): bool
    {
        return $this->authenticationService->isEnabled();
    }

    public function getCurrentAuthenticatedUser(): ?User
    {
        return $this->authenticationService->getCurrentAuthenticatedUser();
    }

    public function hasAuthenticatedUser(): bool
    {
        return $this->authenticationService->hasAuthenticatedUser();
    }

    public function validateWithFields(array $fields): bool
    {
        return $this->authenticationService->validateWithFields($fields);
    }

    public function invalidate()
    {
        $this->authenticationService->invalidate();
    }

    public function getIdField(): string
    {
        return $this->authenticationService->getIdField();
    }

    public function getTableName(): string
    {
        return $this->authenticationService->getTableName();
    }
}
