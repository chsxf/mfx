<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Repository of constants referencing built-in
 * {@see https://github.com/chsxf/mfx/wiki/Configuration-Directives configuration directives}
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class ConfigConstants
{
    public const DATABASE = 'database';
    public const DATABASE_ERROR_LOGGING = self::DATABASE . '.error_logging';
    public const DATABASE_SERVERS = self::DATABASE . '.servers';
    public const DATABASE_UPDATERS_CLASSES = self::DATABASE . '.updaters.classes';
    public const DATABASE_UPDATERS_DOMAIN = self::DATABASE . '.updaters.domain';

    public const REQUEST = 'request';
    public const REQUEST_DEFAULT_ROUTE = self::REQUEST . '.default_route';
    public const REQUEST_POST_ROUTE_CALLBACK = self::REQUEST . '.post_route_callback';
    public const REQUEST_PRE_ROUTE_CALLBACK = self::REQUEST . '.pre_route_callback';
    public const REQUEST_PREFIX = self::REQUEST . '.prefix';

    public const RESPONSE = 'response';
    public const RESPONSE_DEFAULT_CHARSET = self::RESPONSE . '.default_charset';
    public const RESPONSE_DEFAULT_CONTENT_TYPE = self::RESPONSE . '.default_content_type';
    public const RESPONSE_FULL_ERRORS = self::RESPONSE . '.full_errors';

    public const ROUTER = 'router';
    public const ROUTER_CLASS = self::ROUTER . '.class';
    public const ROUTER_OPTIONS_ALLOW_DEFAULT_ROUTE_SUBSTITUTION = self::ROUTER . '.options.allow_default_route_substitution';
    public const ROUTER_OPTIONS_ALLOWED_NAMESPACES = self::ROUTER . '.options.allowed_namespaces';

    public const SESSION = 'session';
    public const SESSION_DOMAIN = self::SESSION . '.domain';
    public const SESSION_ENABLED = self::SESSION . '.enabled';
    public const SESSION_LIFETIME = self::SESSION . '.lifetime';
    public const SESSION_NAME = self::SESSION . '.name';
    public const SESSION_PATH = self::SESSION . '.path';

    public const TWIG = 'twig';
    public const TWIG_CACHE = self::TWIG . '.cache';
    public const TWIG_EXTENSIONS = self::TWIG . '.extensions';
    public const TWIG_TEMPLATES = self::TWIG . '.templates';

    public const USER_MANAGEMENT = 'user_management';
    public const USER_MANAGEMENT_ENABLED = self::USER_MANAGEMENT . '.enabled';
    public const USER_MANAGEMENT_CLASS = self::USER_MANAGEMENT . '.class';
    public const USER_MANAGEMENT_ID_FIELD = self::USER_MANAGEMENT . '.id_field';
    public const USER_MANAGEMENT_TABLE = self::USER_MANAGEMENT . '.table';

    public const BASE_HREF = 'base_href';
    public const DEFAULT_LOCALE = 'default_locale';
    public const FAKE_PROTOCOLS = 'fake_protocols';
    public const PROFILING = 'profiling';
    public const RELATIVE_BASE_HREF = 'mfx_relative_base_href';
    public const SCRIPTS = 'scripts';
    public const STYLESHEETS = 'stylesheets';
    public const TEXT_DOMAINS = 'text_domains';
    public const TIMEZONE = 'timezone';
}
