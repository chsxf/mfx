<?php

use chsxf\MFX\ErrorManager;

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
