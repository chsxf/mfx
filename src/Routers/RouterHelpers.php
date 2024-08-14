<?php

declare(strict_types=1);

namespace chsxf\MFX\Routers;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\HttpStatusCodes;

/**
 * @since 1.0
 */
final class RouterHelpers
{
    /**
     * Checks if the request could be referring to a missing file and replies a 404 HTTP error code
     * @param array $routeParams Request route parameters
     */
    public static function check404file(array $routeParams)
    {
        if (!empty($routeParams) && preg_match('/\.[a-z0-9]+$/i', $routeParams[count($routeParams) - 1])) {
            throw new MFXException(HttpStatusCodes::notFound);
        }
    }
}
