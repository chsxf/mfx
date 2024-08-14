<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\ISessionService;

/**
 * @since 2.0
 * @ignore
 */
final class SessionServiceProxy implements ISessionService
{
    public function __construct(private readonly ISessionService $sessionService)
    {
    }

    public function setInSession(array $values): void
    {
        $this->sessionService->setInSession($values);
    }

    public function unsetInSession(string ...$keys): void
    {
        $this->sessionService->unsetInSession(...$keys);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->sessionService->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->sessionService->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->sessionService->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->sessionService->offsetUnset($offset);
    }
}
