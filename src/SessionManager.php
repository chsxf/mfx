<?php

/**
 * PHP session management
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Session management class
 * @since 1.0
 */
class SessionManager
{
    /**
     * Starts and sets up the PHP session
     * @since 1.0
     */
    public static function start()
    {
        if (empty(Config::get(ConfigConstants::SESSION_ENABLED, true))) {
            return;
        }

        // Setting session parameters
        session_name(Config::get(ConfigConstants::SESSION_NAME, 'MFXSESSION'));
        if (Config::get(ConfigConstants::SESSION_USE_COOKIES, true)) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_trans_id', '0');
            $defaultSessionPath = self::getDefaultCookiePath();
            session_set_cookie_params(Config::get(ConfigConstants::SESSION_LIFETIME, 0), Config::get(ConfigConstants::SESSION_PATH, $defaultSessionPath), Config::get(ConfigConstants::SESSION_DOMAIN, ''));
            session_start();
        } else {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_trans_id', '1');
            if (!empty($_REQUEST[session_name()])) {
                session_id($_REQUEST[session_name()]);
            }
            session_start();
            output_add_rewrite_var(session_name(), session_id());
        }
    }

    /**
     * Retrieves the default cookie path based on current script and framework location
     * @since 1.0
     * @return string
     */
    public static function getDefaultCookiePath(): string
    {
        return preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
    }
}
