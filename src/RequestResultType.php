<?php

/**
 * RequestResultType enum
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Enum used to identify the type of request results
 * @since 1.0
 */
enum RequestResultType: int
{
    /** @since 1.0 */
    case VIEW = 1;
    /** @since 1.0 */
    case REDIRECT = 2;
    /** @since 1.0 */
    case JSON = 3;
    /** @since 1.0 */
    case XML = 4;
    /** @since 1.0 */
    case STATUS = 5;
}
