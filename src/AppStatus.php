<?php

namespace chsxf\MFX;

use chsxf\MFX\Attributes\AnonymousRoute;
use chsxf\MFX\Attributes\Route;
use chsxf\MFX\RequestResult;
use chsxf\MFX\Routers\IRouteProvider;

/**
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 * @ignore
 */
final class AppStatus implements IRouteProvider
{
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
