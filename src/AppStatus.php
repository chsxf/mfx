<?php

namespace chsxf\MFX;

use chsxf\MFX\Attributes\AnonymousRoute;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\RequestResult;
use chsxf\MFX\Routers\IRouteProvider;

/**
 * @since 1.0
 * @package chsxf\MFX
 */
final class AppStatus implements IRouteProvider
{
    /**
     * @since 1.0
     * @return RequestResult
     */
    #[Route]
    #[AnonymousRoute]
    public static function ping(): RequestResult
    {
        $result = array('result' => true);

        $executionDuration = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $result['request_duration'] = $executionDuration;

        return RequestResult::buildJSONRequestResult($result);
    }
}
