<?php

namespace chsxf\MFX;

/**
 * Repository of constants referencing built-in
 * {@see https://github.com/chsxf/mfx/wiki/Configuration-Directives configuration directives}
 *
 * @see chsxf\MFX\Config
 * @since 1.0
 */
final class ConfigConstants
{
    /** @since 1.0 */
    public const DATABASE = 'database';
    /** @since 1.0 */
    public const DATABASE_ERROR_LOGGING = self::DATABASE . '.error_logging';
    /** @since 1.0 */
    public const DATABASE_SERVERS = self::DATABASE . '.servers';
    /** @since 1.0 */
    public const DATABASE_UPDATERS_CLASSES = self::DATABASE . '.updaters.classes';
    /** @since 1.0 */
    public const DATABASE_UPDATERS_DOMAIN = self::DATABASE . '.updaters.domain';

    /** @since 1.0 */
    public const REQUEST = 'request';
    /** @since 1.0 */
    public const REQUEST_DEFAULT_ROUTE = self::REQUEST . '.default_route';
    /** @since 1.0 */
    public const REQUEST_POST_ROUTE_CALLBACK = self::REQUEST . '.post_route_callback';
    /** @since 1.0 */
    public const REQUEST_PRE_ROUTE_CALLBACK = self::REQUEST . '.pre_route_callback';
    /** @since 1.0 */
    public const REQUEST_PREFIX = self::REQUEST . '.prefix';

    /** @since 1.0 */
    public const RESPONSE = 'response';
    /** @since 1.0 */
    public const RESPONSE_DEFAULT_CHARSET = self::RESPONSE . '.default_charset';
    /** @since 1.0 */
    public const RESPONSE_DEFAULT_CONTENT_TYPE = self::RESPONSE . '.default_content_type';
    /** @since 1.0 */
    public const RESPONSE_FULL_ERRORS = self::RESPONSE . '.full_errors';

    /** @since 1.0 */
    public const ROUTER = 'router';
    /** @since 1.0 */
    public const ROUTER_CLASS = self::ROUTER . '.class';
    /** @since 1.0 */
    public const ROUTER_OPTIONS_ALLOW_DEFAULT_ROUTE_SUBSTITUTION = self::ROUTER . '.options.allow_default_route_substitution';
    /** @since 1.0 */
    public const ROUTER_OPTIONS_ALLOWED_NAMESPACES = self::ROUTER . '.options.allowed_namespaces';

    /** @since 1.0 */
    public const SESSION = 'session';
    /** @since 1.0 */
    public const SESSION_DOMAIN = self::SESSION . '.domain';
    /** @since 1.0 */
    public const SESSION_ENABLED = self::SESSION . '.enabled';
    /** @since 1.0 */
    public const SESSION_LIFETIME = self::SESSION . '.lifetime';
    /** @since 1.0 */
    public const SESSION_NAME = self::SESSION . '.name';
    /** @since 1.0 */
    public const SESSION_PATH = self::SESSION . '.path';
    /** @since 1.0 */
    public const SESSION_USE_COOKIES = self::SESSION . '.use_cookies';

    /** @since 1.0 */
    public const TWIG = 'twig';
    /** @since 1.0 */
    public const TWIG_CACHE = self::TWIG . '.cache';
    /** @since 1.0 */
    public const TWIG_EXTENSIONS = self::TWIG . '.extensions';
    /** @since 1.0 */
    public const TWIG_TEMPLATES = self::TWIG . '.templates';

    /** @since 1.0 */
    public const USER_MANAGEMENT = 'user_management';
    /** @since 1.0 */
    public const USER_MANAGEMENT_CLASS = self::USER_MANAGEMENT . '.class';
    /** @since 1.0 */
    public const USER_MANAGEMENT_ID_FIELD = self::USER_MANAGEMENT . '.id_field';
    /** @since 1.0 */
    public const USER_MANAGEMENT_TABLE = self::USER_MANAGEMENT . '.table';

    /** @since 1.0 */
    public const BASE_HREF = 'base_href';
    /** @since 1.0 */
    public const DEFAULT_LOCALE = 'default_locale';
    /** @since 1.0 */
    public const FAKE_PROTOCOLS = 'fake_protocols';
    /** @since 1.0 */
    public const PROFILING = 'profiling';
    /** @since 1.0 */
    public const RELATIVE_BASE_HREF = 'mfx_relative_base_href';
    /** @since 1.0 */
    public const SCRIPTS = 'scripts';
    /** @since 1.0 */
    public const STYLESHEETS = 'stylesheets';
    /** @since 1.0 */
    public const TEXT_DOMAINS = 'text_domains';
    /** @since 1.0 */
    public const TIMEZONE = 'timezone';
}
