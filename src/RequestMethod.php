<?php

namespace chsxf\MFX;

/**
 * @since 1.0
 */
enum RequestMethod
{
    /** @since 1.0 */
    case GET;
    /** @since 1.0 */
    case HEAD;
    /** @since 1.0 */
    case POST;
    /** @since 1.0 */
    case PUT;
    /** @since 1.0 */
    case DELETE;
    /** @since 1.0 */
    case CONNECT;
    /** @since 1.0 */
    case OPTIONS;
    /** @since 1.0 */
    case TRACE;
    /** @since 1.0 */
    case PATCH;
}
