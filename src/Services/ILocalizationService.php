<?php

namespace chsxf\MFX\Services;

/**
 * Localization service interface
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
interface ILocalizationService
{
    /**
     * Binds a new text domain
     * @since 2.0
     * @param string $key Text domain key
     * @param string $path Text domain path
     * @param string $charset Text domain charset (Defaults to UTF-8)
     */
    public function bindTextDomain(string $key, string $path, string $charset = 'UTF-8');

    /**
     * Gets the current locale from environment
     * @since 2.0
     * @return string
     */
    public function getLocale(): string;

    /**
     * Gets the current language from the current locale
     * @since 2.0
     * @return string
     */
    public function getLanguage(): string;
}
