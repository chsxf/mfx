<?php

declare(strict_types=1);

/**
 * PathRouter (route_provider/route) Router Implementation
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\Routers;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\HttpStatusCodes;
use chsxf\MFX\Services\ICoreServiceProvider;

/**
 * @ignore
 * @since 1.0
 */
class PathRouter implements IRouter
{
    private const ROUTE_REGEXP = '#^[[:alnum:]_]+/[[:alnum:]_]+?$#';

    /**
     * @see chsxf\MFX\IRouter::parseRoute()
     */
    public function parseRoute(ICoreServiceProvider $coreServiceProvider, string $filteredPathInfo, string $defaultRoute): RouterData
    {
        if (empty($filteredPathInfo)) {
            if ($defaultRoute === 'none') {
                throw new MFXException(HttpStatusCodes::notFound);
            }

            $route = $defaultRoute;
            $routeParams = [];
        } else {
            $chunks = explode('/', $filteredPathInfo);
            if (count($chunks) < 2) {
                if ($defaultRoute === 'none') {
                    throw new MFXException(HttpStatusCodes::notFound);
                } else {
                    $route = $defaultRoute;
                    $routeParams = $chunks;
                }
            } else {
                $route = "{$chunks[0]}/{$chunks[1]}";
                $routeParams = array_slice($chunks, 2);
            }
        }

        // Checking route
        if (!preg_match(self::ROUTE_REGEXP, $route)) {
            RouterHelpers::check404file(array_merge([$route], $routeParams));
            throw new MFXException(message: "'{$route}' is not a valid route.");
        }
        list($providerClassName, $routeMethodName) = explode('/', $route);

        return RouterData::create($coreServiceProvider, $route, $routeParams, $providerClassName, $routeMethodName);
    }
}
