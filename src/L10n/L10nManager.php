<?php

/**
 * Localization manager class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\L10n;

use chsxf\MFX\Config;
use chsxf\MFX\ConfigConstants;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\ILocalizationService;

if (!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 6);
}

/**
 * Helper class for managing localization
 * @since 1.0
 */
final class L10nManager implements ILocalizationService
{
    private ?string $detectedLocaleFromRequest = null;

    public function __construct(private readonly IConfigService $configService)
    {
        $locale = $this->getLocale();

        if (PHP_OS_FAMILY == 'Windows' || PHP_OS_FAMILY == 'Darwin') {
            putenv("LANGUAGE={$locale}");
            putenv("LANG={$locale}");
        }

        $locale = ["{$locale}.utf8", "{$locale}.UTF8", "{$locale}.utf-8", "{$locale}.UTF-8", $locale];
        setlocale(LC_MESSAGES, $locale);
        setlocale(LC_CTYPE, $locale);

        // Setting application specific text domains
        $hasDefault = false;
        $appTextDomains = $configService->getValue(ConfigConstants::TEXT_DOMAINS);
        if (!empty($appTextDomains) && is_array($appTextDomains)) {
            $hasDefault = array_key_exists('__default', $appTextDomains);
            foreach ($appTextDomains as $k => $v) {
                $this->bindTextDomain($k, $v);
            }
        }
        if ($hasDefault) {
            textdomain('__default');
        }
    }

    /**
     * Detects the locale to use based on the request
     * @return string
     */
    private function detectLocaleFromRequest(): string
    {
        if ($this->detectedLocaleFromRequest === null) {
            // Locale from $_GET
            $locale = trim(empty($_GET['locale']) ? '' : $_GET['locale']);
            if (!empty($locale)) {
                $sessionCookieParams = session_get_cookie_params();
                setcookie('mfx_locale', $locale, time() + 86400 * 365, $sessionCookieParams['path']);
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
                $locale = $this->configService->getValue(ConfigConstants::DEFAULT_LOCALE, 'en_US');
            }

            $this->detectedLocaleFromRequest = $locale;
        }
        return $this->detectedLocaleFromRequest;
    }

    /**
     * Binds a new text domain
     * @since 2.0
     * @param string $key Text domain key
     * @param string $path Text domain path
     * @param string $charset Text domain charset (Defaults to UTF-8)
     */
    public function bindTextDomain(string $key, string $path, string $charset = 'UTF-8')
    {
        bindtextdomain($key, $path);
        bind_textdomain_codeset($key, $charset);
    }

    /**
     * Gets the current locale from environment
     * @since 2.0
     * @return string
     */
    public function getLocale(): string
    {
        $locale_env = getenv('LANG');
        return ($locale_env === false) ? $this->detectLocaleFromRequest() : $locale_env;
    }

    /**
     * Gets the current language from the current locale
     * @since 2.0
     * @return string
     */
    public function getLanguage(): string
    {
        $locale = explode('_', $this->getLocale());
        return $locale[0];
    }
}
