<?php
/**
 * PHP session management
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX;

/**
 * Session management class
 */
class SessionManager
{
	/**
	 * Starts and sets up the PHP session
	 */
	public static function start() {
		if (empty(Config::get('session.enabled', true)))
			return;
		
		// Setting session parameters
		session_name(Config::get('session.name', 'MFXSESSION'));
		if (Config::get('session.use_cookies', true)) {
			ini_set('session.use_cookies', '1');
			ini_set('session.use_trans_id', '0');
			$defaultSessionPath = self::getDefaultCookiePath();
			session_set_cookie_params(Config::get('session.lifetime', 0), Config::get('session.path', $defaultSessionPath), Config::get('session.domain', ''));
			session_start();
		}
		else {
			ini_set('session.use_cookies', '0');
			ini_set('session.use_trans_id', '1');
			if (!empty($_REQUEST[session_name()]))
				session_id($_REQUEST[session_name()]);
			session_start();
			output_add_rewrite_var(session_name(), session_id());
		}
	}
	
	/**
	 * Retrieves the default cookie path based on current script and framework location
	 * @return string
	 */
	public static function getDefaultCookiePath() {
		return preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
	}
}