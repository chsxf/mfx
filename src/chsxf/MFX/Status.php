<?php

use chsxf\MFX\Attributes\AnonymousRouteAttribute;
use chsxf\MFX\Attributes\SubRouteAttribute;
use chsxf\MFX\IRouteProvider;
use chsxf\MFX\RequestResult;

final class Status implements IRouteProvider {

	#[SubRouteAttribute]
	#[AnonymousRouteAttribute]
	public static function ping(): RequestResult {
		$result = array( 'result' => true );

		$executionDuration = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$result['request_duration'] = $executionDuration;

		return RequestResult::buildJSONRequestResult($result);
	}

}