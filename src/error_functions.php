<?php

declare(strict_types=1);

use chsxf\MFX\ErrorManager;

/**
 * Triggers a notification message
 * @since 1.0
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
 * @since 1.0
 * @param array $notifs
 */
function trigger_notifs(array $notifs)
{
    foreach ($notifs as $n) {
        ErrorManager::handleNotif($n);
    }
}
