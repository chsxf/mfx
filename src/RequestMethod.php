<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Enumeration of request methods
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
enum RequestMethod: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case CONNECT = 'CONNECT';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case PATCH = 'PATCH';
}
