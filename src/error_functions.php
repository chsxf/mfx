<?php

use chsxf\MFX\ErrorManager;

/**
 * Triggers multiple errors from an array
 * @param array $errors
 */
function trigger_errors(array $errors)
{
    if (!empty($errors)) {
        foreach ($errors as $err) {
            ErrorManager::handleError($err['errno'], $err['errstr'], $err['errfile'], $err['errline']);
        }
    }
}

/**
 * Triggers a notification message
 * @param string $message Notification message
 *
 * @uses ErrorManager::handleNotif()
 */
function trigger_notif(string $message)
{
    ErrorManager::handleNotif($message);
}

/**
 * Triggers notification messages from an array
 * @param array $notifs
 */
function trigger_notifs(array $notifs)
{
    foreach ($notifs as $n) {
        ErrorManager::handleNotif($n);
    }
}

/**
 * Triggers errors and notifications from a mixed container
 * @param array|object $container
 */
function trigger_errors_and_notifs(array|object $container)
{
    if (is_object($container)) {
        if (!empty($container->errors)) {
            trigger_errors($container->errors);
            if (!empty($container->notifs)) {
                trigger_notifs($container->notifs);
            }
        }
    } elseif (is_array($container)) {
        if (!empty($container['errors'])) {
            trigger_errors($container['errors']);
            if (!empty($container['notifs'])) {
                trigger_errors($container['notifs']);
            }
        }
    }
}
