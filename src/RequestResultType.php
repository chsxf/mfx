<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Enumeration of the various request result types
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
enum RequestResultType: int
{
    case VIEW = 1;
    case REDIRECT = 2;
    case JSON = 3;
    case XML = 4;
    case STATUS = 5;
}
