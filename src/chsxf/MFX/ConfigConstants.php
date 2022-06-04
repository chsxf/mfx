<?php

namespace chsxf\MFX;

final class ConfigConstants
{
    public const DATABASE_ERROR_LOGGING = 'database.error_logging';
    public const DATABASE_SERVERS = 'database.servers';
    public const DATABASE_UPDATERS_CLASSES = 'database.updaters.classes';
    public const DATABASE_UPDATERS_DOMAIN = 'database.updaters.domain';

    public const REQUEST_DEFAULT_ROUTE = 'request.default_route';
    public const REQUEST_POST_ROUTE_CALLBACK = 'request.post_route_callback';
    public const REQUEST_PRE_ROUTE_CALLBACK = 'request.pre_route_callback';
    public const REQUEST_PREFIX = 'request.prefix';

    public const RESPONSE_DEFAULT_CHARSET = 'response.default_charset';
    public const RESPONSE_DEFAULT_CONTENT_TYPE = 'response.default_content_type';
    public const RESPONSE_FULL_ERRORS = 'response.full_errors';

    public const ROUTER_CLASS = 'router.class';
    public const ROUTER_OPTIONS_ALLOW_DEFAULT_ROUTE_SUBSTITUTION = 'router.options.allow_default_route_substitution';
    public const ROUTER_OPTIONS_ALLOWED_NAMESPACES = 'router.options.allowed_namespaces';

    public const SESSION_DOMAIN = 'session.domain';
    public const SESSION_ENABLED = 'session.enabled';
    public const SESSION_LIFETIME = 'session.lifetime';
    public const SESSION_NAME = 'session.name';
    public const SESSION_PATH = 'session.path';
    public const SESSION_USE_COOKIES = 'session.use_cookies';

    public const TWIG_CACHE = 'twig.cache';
    public const TWIG_EXTENSIONS = 'twig.extensions';
    public const TWIG_TEMPLATES = 'twig.templates';

    public const USER_MANAGEMENT_CLASS = 'user_management.class';
    public const USER_MANAGEMENT_KEY_FIELD = 'user_management.key_field';
    public const USER_MANAGEMENT_TABLE = 'user_management.table';

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
