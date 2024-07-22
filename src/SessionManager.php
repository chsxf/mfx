<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\ISessionService;

final class SessionManager implements ISessionService
{
    private const STATUS = 'status';
    private const LAST_UPDATE = 'last_update';
    private const NEW_SESSION_ID = 'new_session_id';

    private static ?SessionManager $singleInstance = null;

    private bool $enabled;

    public function __construct(private readonly IConfigService $configService)
    {
        if (self::$singleInstance !== null) {
            throw new MFXException(HttpStatusCodes::internalServerError, "SessionManager has already been instantiated");
        }

        $this->enabled = !empty($this->configService->getValue(ConfigConstants::SESSION_ENABLED, true));
        if ($this->enabled) {
            $this->setupSession();
            $this->validateSession();
        }
    }

    private function setupSession()
    {
        // Setting recommended session options
        // See: https://www.php.net/manual/en/session.security.ini.php
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_trans_id', '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bit_per_character', '6');
        ini_set('session.hash_function', 'sha256');
        ini_set('session.gc_maxlifetime', '900'); // 15 minutes

        // Setting additional session options
        ini_set('session.serialize_handler', 'php_serialize');

        // Setting session parameters
        session_name($this->configService->getValue(ConfigConstants::SESSION_NAME, 'MFXSESSION'));
        session_set_cookie_params(
            $this->configService->getValue(ConfigConstants::SESSION_LIFETIME, 0),
            $this->configService->getValue(ConfigConstants::SESSION_PATH, ''),
            $this->configService->getValue(ConfigConstants::SESSION_DOMAIN, '')
        );
    }

    private function validateSession()
    {
        session_start();
        if (empty($_SESSION[self::STATUS])) {
            $this->setSessionActive();
            session_commit();
        } else if ($_SESSION[self::STATUS] == SessionStatus::deleted->value) {
            $this->initializeNewSession(false);
            session_commit();
        } else if ($_SESSION[self::STATUS] == SessionStatus::migrated->value) {
            if ($_SESSION[self::LAST_UPDATE] < time() - 30) {
                $this->markSessionAsDeleted();
                $this->initializeNewSession(true);
                session_commit();
            } else if (!empty($_SESSION[self::NEW_SESSION_ID])) {
                $migratedId = $_SESSION[self::NEW_SESSION_ID];
                session_abort();
                session_id($migratedId);
                $this->validateSession();
            } else {
                $this->initializeNewSession(false);
                session_commit();
            }
        } else if ($_SESSION[self::STATUS] == SessionStatus::active->value && $_SESSION[self::LAST_UPDATE] < time() - 900) {
            $sessionCopy = $_SESSION;

            $migratedId = session_create_id();
            $_SESSION[self::STATUS] = SessionStatus::migrated->value;
            $_SESSION[self::NEW_SESSION_ID] = $migratedId;
            $_SESSION[self::LAST_UPDATE] = time();
            session_commit();

            session_id($migratedId);
            session_start();
            $_SESSION = $sessionCopy;
            $_SESSION[self::LAST_UPDATE] = time();
            session_commit();
        } else {
            $this->initializeNewSession(false);
            session_commit();
        }
    }

    private function initializeNewSession(bool $commitCurrentSession)
    {
        $newId = session_create_id();
        $commitCurrentSession ? session_commit() : session_abort();
        session_abort();
        session_id($newId);
        session_start();
        $this->setSessionActive();
    }

    private function setSessionActive()
    {
        $_SESSION[self::STATUS] = SessionStatus::active->value;
        $_SESSION[self::LAST_UPDATE] = time();
    }

    private function markSessionAsDeleted()
    {
        $_SESSION[self::STATUS] = SessionStatus::deleted->value;
        $_SESSION[self::LAST_UPDATE] = time();
    }

    public function setInSession(array $values)
    {
        if ($this->enabled && !empty($values)) {
            session_start();
            foreach ($values as $k => $v) {
                $_SESSION[$k] = $v;
            }
            session_commit();
        }
    }

    public function unsetInSession(string ...$keys)
    {
        if ($this->enabled && !empty($keys)) {
            session_start();
            foreach ($keys as $key) {
                unset($_SESSION[$key]);
            }
            session_commit();
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->enabled && isset($_SESSION[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->enabled ? $_SESSION[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->enabled) {
            session_start();
            $_SESSION[$offset] = $value;
            session_commit();
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->enabled) {
            session_start();
            unset($_SESSION[$offset]);
            session_commit();
        }
    }
}
