<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\ISessionService;
use Twig\Environment;

/**
 * Integrated error management class
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class ErrorManager
{
    private const CATCHABLES = array(
        'E_NOTICE' => E_NOTICE,
        'E_WARNING' => E_WARNING,
        'E_USER_ERROR' => E_USER_ERROR,
        'E_USER_WARNING' => E_USER_WARNING,
        'E_USER_NOTICE' => E_USER_NOTICE,
        'E_RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
        'E_STRICT' => E_STRICT,
        'E_DEPRECATED' => E_DEPRECATED
    );

    /**
     * @var array Errors container
     */
    private array $errors = array();
    /**
     * @var array Notifications container
     */
    private array $notifs = array();

    /**
     * @var callable Reference to the previous error handler, or NULL if none exists
     */
    private $previousHandler = null;

    private static ?ErrorManager $singleInstance = null;

    /**
     * Constructor
     * @since 2.0
     * @param IConfigService $configService Config service instance
     * @param ISessionService $sessionService Session service instance
     * @throws MFXException
     */
    public function __construct(private readonly IConfigService $configService, private readonly ISessionService $sessionService)
    {
        if (self::$singleInstance !== null) {
            throw new MFXException(HttpStatusCodes::internalServerError, "ErrorManager has already been instantiated");
        }
        self::$singleInstance = $this;

        $this->previousHandler = set_error_handler($this->handleError(...));
        $this->unfreeze();
    }

    /**
     * Gets the constant name string from the error number
     * @since 2.0
     * @param int $errno Error number
     * @return string|boolean the name string of false if the error number is not catchable
     */
    private function getConstantFromErrorNumber(int $errno): string|false
    {
        foreach (self::CATCHABLES as $k => $v) {
            if ($v == $errno) {
                return $k;
            }
        }
        return false;
    }

    /**
     * Tells if the manager holds at least one error.
     * @since 2.0
     * @return boolean
     */
    public function hasError(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Tells if the manager holds at least one notification.
     * @since 2.0
     * @return boolean
     */
    public function hasNotif(): bool
    {
        return !empty($this->notifs);
    }

    /**
     * Handles errors
     *
     * @ignore
     * @since 2.0
     *
     * @param int $errno Error number/level
     * @param string $errstr Error message
     * @param string $errfile Filename from which the error was raised
     * @param int $errline Line number from which the error was raised
     * @return boolean
     *
     * @see http://php.net/manual/en/function.set-error-handler.php
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (error_reporting() && $errno) {
            if (($constant = $this->getConstantFromErrorNumber($errno)) !== false) {
                $errdata = array(
                    'errno' => $errno,
                    'errstr' => $errstr,
                    'errnoconstant' => $constant
                );
                if ($this->configService->getValue(ConfigConstants::RESPONSE_FULL_ERRORS, false)) {
                    $errdata['errfile'] = $errfile;
                    $errdata['errline'] = $errline;
                }
                $this->errors[] = $errdata;
                return true;
            }
        }
        return empty($this->previousHandler) ? true : call_user_func($this->previousHandler, $errno, $errstr, $errfile, $errline);
    }

    /**
     * Handles notifications
     *
     * @ignore
     *
     * @param string $message Notification message
     *
     * @used-by trigger_notif()
     */
    public static function handleNotif(string $message)
    {
        if (self::$singleInstance !== null) {
            self::$singleInstance->notifs[] = $message;
        }
    }

    /**
     * Freezes the error manager state into session data
     * @since 2.0
     * @param bool $flush If set, flushes error containers. (Defaults to false)
     */
    public function freeze(bool $flush = false)
    {
        if ($this->hasError() || $this->hasNotif()) {
            $arr = [];
            if ($this->hasError()) {
                $arr['errors'] = $this->errors;
            }
            if ($this->hasNotif()) {
                $arr['notifs'] = $this->notifs;
            }

            $this->sessionService[__CLASS__] = $arr;
        } else {
            unset($this->sessionService[__CLASS__]);
        }

        if (!empty($flush)) {
            $this->flush();
        }
    }

    /**
     * Unfreezes the error manager state from session data if applying
     * @since 2.0
     */
    private function unfreeze()
    {
        if (isset($this->sessionService[__CLASS__])) {
            $arr = $this->sessionService[__CLASS__];
            if (!empty($arr['errors'])) {
                $this->errors = array_merge($this->errors, $arr['errors']);
            }
            if (!empty($arr['notifs'])) {
                $this->notifs = array_merge($this->notifs, $arr['notifs']);
            }

            unset($this->sessionService[__CLASS__]);
        }
    }

    /**
     * Flushes error and notification messages for template display
     * @since 2.0
     * @param \Twig_Environment $twig Twig environment. If NULL, the function flushes containers only and returns an empty string
     * @return string
     */
    public function flush(?Environment $twig = null): string
    {
        if ($twig !== null) {
            $str = $twig->render('@mfx/ErrorManager.twig', array('errors' => $this->errors, 'notifs' => $this->notifs, 'debug' => $this->configService->getValue(ConfigConstants::RESPONSE_FULL_ERRORS, false)));
        } else {
            $str = '';
        }
        $this->errors = array();
        $this->notifs = array();
        return $str;
    }

    /**
     * Flushes error and notification messages to an array or an object
     * @since 2.0
     * @param array|object $arrOrObject Array or object to modify
     */
    public function flushToArrayOrObject(array|object &$arrOrObject)
    {
        if (is_array($arrOrObject)) {
            if (empty($arrOrObject) || !array_is_list($arrOrObject)) {
                $this->flushToArray($arrOrObject);
            }
        } elseif (is_object($arrOrObject)) {
            $this->flushToObject($arrOrObject);
        }
        $this->flush();
    }

    /**
     * Flushes error and notification messages to an array
     * @since 2.0
     * @param array $arr
     */
    private function flushToArray(array &$arr)
    {
        if ($this->hasError()) {
            $arr['errors'] = $this->errors;
        }
        if ($this->hasNotif()) {
            $arr['notifs'] = $this->notifs;
        }
    }

    /**
     * Flushes error and notification messages to an object
     * @since 2.0
     * @param object $object
     */
    private function flushToObject(object $object)
    {
        if ($this->hasError()) {
            $object->errors = $this->errors;
        }
        if ($this->hasNotif()) {
            $object->notifs = $this->notifs;
        }
    }
}
