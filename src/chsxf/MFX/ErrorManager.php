<?php

/**
 * Global error manager
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use Twig\Environment;

/**
 * Integrated error management class
 * @since 1.0
 */
class ErrorManager
{
    /**
     * @var array Key-value pairs of catchable error codes
     */
    private static array $_CATCHABLE = array(
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
    private static array $_errors = array();
    /**
     * @var array Notifications container
     */
    private static array $_notifs = array();

    /**
     * @var callable Reference to the previous error handler, or NULL if none exists
     */
    public static $previousHandler = null;

    /**
     * Gets the constant name string from the error number
     * @param int $errno Error number
     * @return string|boolean the name string of false if the error number is not catchable
     */
    private static function _getConstantFromErrorNumber(int $errno): string|false
    {
        foreach (self::$_CATCHABLE as $k => $v) {
            if ($v == $errno) {
                return $k;
            }
        }
        return false;
    }

    /**
     * Tells if the manager holds at least one error.
     * @since 1.0
     * @return boolean
     */
    public static function hasError(): bool
    {
        return !empty(self::$_errors);
    }

    /**
     * Tells if the manager holds at least one notification.
     * @since 1.0
     * @return boolean
     */
    public static function hasNotif(): bool
    {
        return !empty(self::$_notifs);
    }

    /**
     * Handles errors
     *
     * @ignore
     *
     * @param int $errno Error number/level
     * @param string $errstr Error message
     * @param string $errfile Filename from which the error was raised
     * @param int $errline Line number from which the error was raised
     * @return boolean
     *
     * @see http://php.net/manual/en/function.set-error-handler.php
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (error_reporting() && $errno) {
            if (($constant = self::_getConstantFromErrorNumber($errno)) !== false) {
                $errdata = array(
                    'errno' => $errno,
                    'errstr' => $errstr,
                    'errnoconstant' => $constant
                );
                if (Config::get(ConfigConstants::RESPONSE_FULL_ERRORS, false)) {
                    $errdata['errfile'] = $errfile;
                    $errdata['errline'] = $errline;
                }
                self::$_errors[] = $errdata;
                return true;
            }
        }
        return empty(self::$previousHandler) ? true : call_user_func(self::$previousHandler, $errno, $errstr, $errfile, $errline);
    }

    /**
     * Handle notifications
     *
     * @ignore
     *
     * @param string $message Notification message
     *
     * @used-by trigger_notif()
     */
    public static function handleNotif(string $message)
    {
        self::$_notifs[] = $message;
    }

    /**
     * Freezes the error manager state into session data
     * @since 1.0
     * @param bool $flush If set, flushes error containers. (Defaults to false)
     */
    public static function freeze(bool $flush = false)
    {
        $_SESSION[__CLASS__] = serialize(array('errors' => self::$_errors, 'notifs' => self::$_notifs));
        if (!empty($flush)) {
            ErrorManager::flush();
        }
    }

    /**
     * Unfreezes the error manager state from session data if applying
     * @since 1.0
     */
    public static function unfreeze()
    {
        if (!empty($_SESSION[__CLASS__])) {
            $arr = @unserialize($_SESSION[__CLASS__]);
            if (!empty($arr)) {
                self::$_errors = array_merge(self::$_errors, $arr['errors']);
                self::$_notifs = array_merge(self::$_notifs, $arr['notifs']);
            }
            unset($_SESSION[__CLASS__]);
        }
    }

    /**
     * Flushes error and notification messages for template display
     * @since 1.0
     * @param \Twig_Environment $twig Twig environment. If NULL, the function flushes containers only and returns an empty string
     * @return string
     */
    public static function flush(?Environment $twig = null): string
    {
        if ($twig !== null) {
            $str = $twig->render('@mfx/ErrorManager.twig', array('errors' => self::$_errors, 'notifs' => self::$_notifs, 'debug' => Config::get(ConfigConstants::RESPONSE_FULL_ERRORS, false)));
        } else {
            $str = '';
        }
        self::$_errors = array();
        self::$_notifs = array();
        return $str;
    }

    /**
     * Flushes error and notification messages to an array or an object
     * @since 1.0
     * @param array|object $arrOrObject Array or object to modify
     */
    public static function flushToArrayOrObject(array|object &$arrOrObject)
    {
        if (is_array($arrOrObject)) {
            self::flushToArray($arrOrObject);
        } elseif (is_object($arrOrObject)) {
            self::flushToObject($arrOrObject);
        }
    }

    /**
     * Flushes error and notification messages to an array
     * @since 1.0
     * @param array $arr
     */
    public static function flushToArray(array &$arr)
    {
        if (!empty(self::$_errors)) {
            $arr['errors'] = self::$_errors;
        }
        if (!empty(self::$_notifs)) {
            $arr['notifs'] = self::$_notifs;
        }
        ErrorManager::flush();
    }

    /**
     * Flushes error and notification messages to an object
     * @since 1.0
     * @param object $object
     */
    public static function flushToObject(object $object)
    {
        if (!empty(self::$_errors)) {
            $object->errors = self::$_errors;
        }
        if (!empty(self::$_notifs)) {
            $object->notifs = self::$_notifs;
        }
        ErrorManager::flush();
    }
}

ErrorManager::$previousHandler = set_error_handler(array(ErrorManager::class, 'handleError'));
