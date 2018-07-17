<?php
use CheeseBurgames\MFX\IRouteProvider;
use CheeseBurgames\MFX\RequestResult;

final class Status implements IRouteProvider {

	/**
	 * @mfx_subroute
	 * @mfx_anonymous
	 */
	public static function ping() {
		$result = array( 'result' => true );

		$executionDuration = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$result['request_duration'] = $executionDuration;

		return RequestResult::buildJSONRequestResult($result);
	}

}