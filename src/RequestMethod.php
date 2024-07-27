<?php

namespace chsxf\MFX;

/**
 * Enumeration of request methods
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
enum RequestMethod
{
    case GET;
    case HEAD;
    case POST;
    case PUT;
    case DELETE;
    case CONNECT;
    case OPTIONS;
    case TRACE;
    case PATCH;
}
