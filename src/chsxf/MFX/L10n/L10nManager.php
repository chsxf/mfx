<?php

/**
 * Localization manager class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\L10n;

use \chsxf\MFX\Config;
use \chsxf\MFX\SessionManager;

if (!defined('LC_MESSAGES')) {
	define('LC_MESSAGES', 6);
}

/**
 * Helper class for managing localization
 */
class L10nManager
{
	/**
	 * Detects the locale to use based on the request
	 * @return string
	 */
	private static function detectLocaleFromRequest(): string
	{
		// Locale from $_GET
		$locale = trim(empty($_GET['locale']) ? '' : $_GET['locale']);
		if (!empty($locale)) {
			setcookie('mfx_locale', $locale, time() + 86400 * 365, SessionManager::getDefaultCookiePath());
		}

		// Locale from $_COOKIE
		if (empty($locale) && !empty($_COOKIE['mfx_locale'])) {
			$locale = $_COOKIE['mfx_locale'];
		}

		// Locale from $_SERVER
		if (empty($locale) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			array_walk($locales, function (&$item) {
				$item = preg_replace('/;.+$/', '', $item);
			});
			$locales = array_filter($locales, function ($item) {
				return preg_match('/^[a-z]{2}-[a-z]{2}$/i', $item);
			});
			array_walk($locales, function (&$item) {
				$chunks = explode('-', $item);
				$item = sprintf("%s_%s", $chunks[0], strtoupper($chunks[1]));
			});
			$locales = array_unique(array_values($locales));
			if (!empty($locales)) {
				$locale = $locales[0];
			}
		}

		// Default locale from config
		if (empty($locale)) {
			$locale = Config::get('default_locale', 'en_US');
		}

		return $locale;
	}

	/**
	 * Initializes the localization manager
	 */
	public static function init()
	{
		$locale = self::getLocale();

		putenv("LANGUAGE={$locale}");
		putenv("LANG={$locale}");

		$locale = array("{$locale}.utf8", "{$locale}.UTF8", "{$locale}.utf-8", "{$locale}.UTF-8", $locale);
		setlocale(LC_MESSAGES, $locale);
		setlocale(LC_CTYPE, $locale);

		// Setting application specific text domains
		$hasDefault = false;
		$appTextDomains = Config::get('text_domains');
		if (!empty($appTextDomains) && is_array($appTextDomains)) {
			$hasDefault = array_key_exists('__default', $appTextDomains);
			foreach ($appTextDomains as $k => $v)
				self::bindTextDomain($k, $v);
		}
		if ($hasDefault) {
			textdomain('__default');
		}
	}

	/**
	 * Binds a new text domain
	 * @param string $key Text domain key
	 * @param string $path Text domain path
	 * @param string $charset Text domain charset (Defaults to UTF-8)
	 */
	public static function bindTextDomain(string $key, string $path, string $charset = 'UTF-8')
	{
		bindtextdomain($key, $path);
		bind_textdomain_codeset($key, $charset);
	}

	/**
	 * Gets the current locale from environment
	 * @return string
	 */
	public static function getLocale(): string
	{
		$locale_env = getenv('LANG');
		return ($locale_env === false) ? self::detectLocaleFromRequest() : $locale_env;
	}

	/**
	 * Gets the current language from the current locale
	 * @return string
	 */
	public static function getLanguage(): string
	{
		$locale = explode('_', self::getLocale());
		return $locale[0];
	}
}
