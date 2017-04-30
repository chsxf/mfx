<?php
/**
 * Core manager
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 * 
 * @license GPL, version 2
 */

namespace CheeseBurgames\MFX;

/**
 * Core manager singleton class
 * 
 * Handles all requests and responses.
 */
final class CoreManager
{
	private const ROUTE_REGEXP = '/^[[:alnum:]_]+\.[[:alnum:]_]+?$/';
	
	private static $HTTP_STATUS_CODES = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',
			310 => 'Too many Redirects',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range unsatisfiable',
			417 => 'Expectation failed',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			449 => 'Retry With',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not extended',
			511 => 'Network authentication required',
			520 => 'Web server is returning an unknown error'
	);
	
	/**
	 * @var CoreManager Single instance of the class
	 */
	private static $_singleInstance = NULL;
	
	/**
	 * @var DocCommentParser Current documentation comment parser
	 */
	private $_docCommentParser;
	
	/**
	 * @var array Fake protocols list (keys are protocols names and values replacement strings)
	 */
	private $_fakeProtocols = array();
	
	/**
	 * @var string Root URI container (as built from server information)
	 */
	private $_rootURI = NULL;
	
	/**
	 * Ensures the singleton class instance has been correctly initialized only once
	 * @return CoreManager the singleton class instance
	 */
	private static function _ensureInit() {
		if (self::$_singleInstance === NULL)
		{
			self::$_singleInstance = new CoreManager();
			self::setDocCommentParser(new DocCommentParser());
			
			// Fake protocols
			$mfxRelativeBaseHREF = Config::get('mfx_relative_base_href', 'mfx');
			if ('/' !== $mfxRelativeBaseHREF)
				$mfxRelativeBaseHREF = rtrim($mfxRelativeBaseHREF, '/');
			self::$_singleInstance->_fakeProtocols = array(
					'mfxjs' => "{$mfxRelativeBaseHREF}/static/js/",
					'mfxcss' => "{$mfxRelativeBaseHREF}/static/css/",
					'mfximg' => "{$mfxRelativeBaseHREF}/static/img/"
			);
			$fakeProtocols = Config::get('fake_protocols', array());
			if (is_array($fakeProtocols))
			{
				$definedWrappers = stream_get_wrappers();
				foreach ($fakeProtocols as $k => $v)
				{
					if (in_array($k, $definedWrappers) || array_key_exists($k, self::$_singleInstance->_fakeProtocols) || !preg_match('/^\w+$/', $k))
						continue;
			
					// Trailing with a back slash
					if (!preg_match('#/$#', $v) && $v != '')
						$v .= '/';
			
					self::$_singleInstance->_fakeProtocols[$k] = $v;
				}
			}
			ob_start(array(__CLASS__, 'convertFakeProtocols'));
			
			if (Config::get('response.default_content_type', 'text/html') == 'text/html') {
				// Adding scripts
				Scripts::add('mfxjs://jquery.min.js');
				Scripts::add('mfxjs://layout.js');
				Scripts::add('mfxjs://ui.js');
				Scripts::add('mfxjs://mainObserver.js');
				Scripts::add('mfxjs://string.js');
				$userScripts = Config::get('scripts', array());
				if (is_array($userScripts))
				{
					foreach ($userScripts as $s)
						Scripts::add($s);
				}
				
				// Adding stylesheets
				StyleSheets::add('mfxcss://framework.css');
				$userSheets = Config::get('stylesheets', array());
				if (is_array($userSheets))
				{
					foreach ($userSheets as $s)
						StyleSheets::add($s);
				}
			}
		}
		return self::$_singleInstance;
	}
	
	/**
	 * Converts the fake protocols in the input strings
	 * @param string $str Input string
	 * 
	 * @return string
	 */
	public static function convertFakeProtocols($str) {
		$inst = self::_ensureInit();
		$search = array();
		foreach (array_keys($inst->_fakeProtocols) as $k)
			$search[] = "{$k}://";
		return str_replace($search, array_values($inst->_fakeProtocols), $str);
	}
	
	/**
	 * Sets the current documentation comment parser
	 * 
	 * @param DocCommentParser $parser New documentation comment parser. If empty, the current parser remains active.
	 * @return DocCommentParser the previous parser 
	 */
	public static function setDocCommentParser(DocCommentParser $parser) {
		$inst = self::_ensureInit();
		
		$currentParser = $inst->_docCommentParser;
		if (!empty($parser))
			$inst->_docCommentParser = $parser;
		return $currentParser;
	}
	
	/**
	 * Checks if a specific method is a valid sub-route
	 * @param \ReflectionMethod $method Method to inspect
	 * @return boolean|array An array containing the valid sub-route parameters or false in case of an error
	 */
	public static function isMethodValidSubRoute(\ReflectionMethod $method) {
		// Checking method
		$params = $method->getParameters();
		if (!$method->isStatic() || !$method->isPublic() || (count($params) >= 1 && !$params[0]->isArray()))
			return false;	
		// Building parameters from doc comment
		$validSubRouteParameters = self::_ensureInit()->_docCommentParser->parse($method);
		if (!isset($validSubRouteParameters['mfx_subroute']))
			return false;
		return $validSubRouteParameters;
	}
	
	/**
	 * Handles the request sent to the server
	 * 
	 * @param string $defaultRoute Route to use if none can be guessed from request
	 */
	public static function handleRequest(\Twig_Environment $twig, $defaultRoute) {
		$inst = self::_ensureInit();

		// Finding route from REQUEST_URI
		$prefix = preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
		if (!preg_match('#/$#', $prefix))
			$prefix .= '/';
		$prefix .= Config::get('request.prefix', '');
		if (!preg_match('#/$#', $prefix))
			$prefix .= '/';
		$routePathInfo = substr($_SERVER['REQUEST_URI'], strlen($prefix));
		$routePathInfo = explode('?', $routePathInfo, 2);
		$routePathInfo = ltrim($routePathInfo[0], '/');
		
		// Guessing route from path info
		if (empty($routePathInfo))
		{
			if ($defaultRoute == 'none')
				self::dieWithStatusCode(200);
			
			$route = $defaultRoute;
			$routeParams = array();
		}
		else
		{
			$chunks = explode('/', $routePathInfo, 2);
			$route = $chunks[0];
			$firstRouteParam = 1;
			if (!preg_match(self::ROUTE_REGEXP, $route) && Config::get('allow_default_route_substitution', false)) {
				$route = $defaultRoute;
				$firstRouteParam = 0;
			}
			$routeParams = (empty($chunks[$firstRouteParam]) && (!isset($chunks[$firstRouteParam]) || $chunks[$firstRouteParam] !== '0')) ? array() : explode('/', $chunks[$firstRouteParam]);
		}
		
		// Checking route
		if (!preg_match(self::ROUTE_REGEXP, $route)) {
			self::_check404file($routeParams);
			throw new \ErrorException("'{$route}' is not a valid route.");
		}
		list($mainRoute, $subRoute) = explode('.', $route);
		try {
			$rc = new \ReflectionClass($mainRoute);
		}
		catch (\ReflectionException $e) {
			try {
				$rc = new \ReflectionClass(__NAMESPACE__."\\{$mainRoute}");
			}
			catch (\ReflectionException $e) {
				self::_check404file($routeParams);
				throw $e;
			}
		}
		if (!$rc->implementsInterface(IRouteProvider::class))
			throw new \ErrorException("'{$mainRoute}' is not a valid route provider.");
		$validRouteProviderParameters = $inst->_docCommentParser->parse($rc);
		
		// Checking subroute
		$rm = $rc->getMethod($subRoute);
		$validSubRouteParameters = self::isMethodValidSubRoute($rm);
		if (false === $validSubRouteParameters)
			throw new \ErrorException("'{$subRoute}' is not a valid subroute of the '{$mainRoute}' route.");
		
		// Pre-processing callbacks
		// -- Global
		$callback = Config::get('request.pre_route_callback');
		if (!empty($callback) && is_callable($callback))
			call_user_func($callback, $mainRoute, $subRoute, $validRouteProviderParameters, $validSubRouteParameters);
		// -- Route
		if (array_key_exists('mfx_pre_route_callback', $validRouteProviderParameters)) {
			$callback = $validRouteProviderParameters['mfx_pre_route_callback'];
			if (!empty($callback) && is_callable($callback))
				call_user_func($callback, $mainRoute, $subRoute, $validRouteProviderParameters, $validSubRouteParameters);
		}
		
		// Processing route
		$reqResult = $rm->invoke(NULL, $routeParams);
		$routeProvidedTemplate = array_key_exists('mfx_template', $validSubRouteParameters) ? $validSubRouteParameters['mfx_template'] : NULL;
		switch ($reqResult->subRouteType()->value())
		{
			// Views
			case SubRouteType::VIEW:
				if ($reqResult->statusCode() != 200)
					self::dieWithStatusCode($reqResult->statusCode());
				
				CoreProfiler::pushEvent('Building response');
				self::_setResponseContentType($validSubRouteParameters, Config::get('response.default_content_type', 'text/html'), Config::get('response.default_charset', 'UTF-8'));
				$template = $reqResult->template(($routeProvidedTemplate === NULL) ? str_replace(array('_', '.'), '/', $route) : $routeProvidedTemplate);
				
				$context = array_merge($reqResult->data(), array(
						'mfx_scripts' => Scripts::export($twig),
						'mfx_stylesheets' => StyleSheets::export($twig),
						'mfx_root_url' => Config::get('base_href', self::_buildRootURI()),
						'mfx_errors_and_notifs' => ErrorManager::flush($twig),
						'mfx_current_user' => User::currentUser()
				));
				
				$twig->display($template, $context);
				CoreProfiler::pushEvent('Response built');
				break;
			
			// Edit requests - Mostly requests with POST data
			case SubRouteType::EDIT:
				$redirectionURI = $reqResult->redirectURI();
				if (empty($redirectionURI) && !empty($validSubRouteParameters['mfx_redirect_uri']))
					$redirectionURI = $validSubRouteParameters['mfx_redirect_uri'];
				self::redirect($redirectionURI);
				break;
				
			// Asynchronous requests expecting JSON data
			case SubRouteType::JSON:
				self::outputJSON($reqResult, $validSubRouteParameters, $twig);
				break;
				
			// Asynchronous requests expecting XML data
			case SubRouteType::XML:
				self::outputXML($reqResult, $validSubRouteParameters, $twig);
				break;
				
			// Status
			case SubRouteType::STATUS:
				self::outputStatusCode($reqResult->statusCode(), $reqResult->data());
				break;
		}
		
		// Post-processing callback
		$callback = Config::get('request.post_route_callback');
		if (!empty($callback) && is_callable($callback))
			call_user_func($callback, $mainRoute, $subRoute, $validRouteProviderParameters, $validSubRouteParameters);
	}
	
	private static function outputJSON(RequestResult $reqResult, array $subRouteParameters = array(), \Twig_Environment $twig = NULL) {
		self::_setStatusCode($reqResult->statusCode());
		self::_setResponseContentType($subRouteParameters, 'application/json', Config::get('response.default_charset', 'UTF-8'));
		if ($twig != NULL && $reqResult->preformatted())
		{
			ErrorManager::flush();
			echo $twig->render($reqResult->data(), array('mfx_current_user' => User::currentUser()));
		}
		else
		{
			$d = $reqResult->data();
			ErrorManager::flushToArrayOrObject($d);
			echo JSONTools::filterAndEncode($d);
		}
	}
	
	private static function outputXML(RequestResult $reqResult, array $subRouteParamters = array(), \Twig_Environment $twig = NULL) {
		self::_setStatusCode($reqResult->statusCode());
		self::_setResponseContentType($subRouteParamters, 'application/xml', Config::get('response.default_charset', 'UTF-8'));
		if ($twig != NULL && $reqResult->preformatted())
		{
			ErrorManager::flush();
			echo $twig->render($reqResult->data(), array('mfx_current_user' => User::currentUser()));
		}
		else
		{
			$d = $reqResult->data();
			ErrorManager::flushToArrayOrObject($d);
			echo XMLTools::build($reqResult->data());
		}
	}
	
	/**
	 * Checks if the request could be referring to a missing file and replies a 404 HTTP error code
	 * @param array $routeParams Request route parameters
	 */
	private static function _check404file(array $routeParams) {
		if (!empty($routeParams) && preg_match('/\.[a-z0-9]+$/i', $routeParams[count($routeParams) - 1]))
			self::dieWithStatusCode(404);
	}
	
	/**
	 * Builds the root URI from server information (protocol, host and PHP_SELF)
	 * @return string
	 */
	private static function _buildRootURI() {
		$inst = self::_ensureInit();
		if ($inst->_rootURI === NULL)
		{
			$protocol = (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS'] == 'off')) ? 'http' : 'https';
			$inst->_rootURI = "{$protocol}://{$_SERVER['HTTP_HOST']}".preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
			if (!preg_match('#/$#', $inst->_rootURI))
				$inst->_rootURI .= '/';
		}
		return $inst->_rootURI;
	}
	
	/**
	 * Redirects the user the specified URI, the HTTP referer if defined and same host or the website root
	 * @param string $redirectURI Target redirection URI (Defaults to NULL)
	 */
	public static function redirect($redirectURI = NULL)
	{
		if (empty($redirectURI) && !empty($_SERVER['HTTP_REFERER']) && preg_match("#https?://{$_SERVER['HTTP_HOST']}#", $_SERVER['HTTP_REFERER']))
			$redirectURI = $_SERVER['HTTP_REFERER'];
		
		if (empty($redirectURI) || !preg_match('#^https?://#', $redirectURI))
		{
			// Building URI
			$r = self::_buildRootURI();
			if (!empty($redirectURI))
				$r .= ltrim($redirectURI, '/');
		}
		else
			$r = $redirectURI;
		header("Location: $r");
		ErrorManager::freeze();
		exit();
	}
	
	/**
	 * Sets the HTTP status code
	 * @param number $code HTTP status code to emit (Defaults to 200 OK)
	 * @return number the specified status code or 400 if invalid
	 */
	private static function _setStatusCode($code = 200) {
		if (!array_key_exists($code, self::$HTTP_STATUS_CODES))
			$code = 400;
		header(sprintf("HTTP/1.1 %d %s", $code, self::$HTTP_STATUS_CODES[$code]));
		return $code;
	}
	
	/**
	 * Emits a HTTP status code
	 * @param int $code HTTP status code to emit (Defaults to 400 Bad Request)
	 * @param string $message Custom message to output with status code
	 */
	public static function outputStatusCode($code = 400, $message = '') {
		$code = self::_setStatusCode($code);
		
		$contentType = Config::get('response.default_content_type', 'text/plain');
		$charset = Config::get('response.default_charset', 'UTF-8');
		
		$data = array(
				'code' => $code,
				'status' => self::$HTTP_STATUS_CODES[$code]
		);
		if (!empty($message))
			$data['message'] = $message;
			
		self::_setResponseContentType(array(), $contentType, $charset);
		switch ($contentType) {
			case 'application/json':
				$reqResult = RequestResult::buildJSONRequestResult($data, false, $code);
				self::outputJSON($reqResult);
				break;
			case 'application/xml':
				$reqResult = RequestResult::buildXMLRequestResult($data, false, $code);
				self::outputXML($reqResult);
				break;
			default:
				echo "{$data['code']} {$data['status']}";
				if (isset($data['message']))
					echo "\n{$data['message']}";
				break;
		}
	}
	
	/**
	 * Terminates the script and emits a HTTP status code
	 * @param int $code HTTP status code to emit (Defaults to 400 Bad Request)
	 * @param string $message Custom message to output with status code
	 */
	public static function dieWithStatusCode($code = 400, $message = '') {
		self::outputStatusCode($code, $message);
		ErrorManager::freeze();
		exit();
	}
	
	/**
	 * Sets the response Content-Type header from the sub-route documentation comment parameters
	 * 
	 * @param array $subRouteParameters Documentation comment parameters of the sub-route
	 * @param string $default Content type to use if not provided by the sub-route.
	 * @param string $defaultCharset Charset to use if not provided by the sub-route.
	 */
	private static function _setResponseContentType(array $subRouteParameters, $default, $defaultCharset)
	{
		$ct = array_key_exists('mfx_content_type', $subRouteParameters) ? $subRouteParameters['mfx_content_type'] : $default;
		if (!preg_match('/;\s+charset=.+$/', $ct))
			$ct .= "; charset={$defaultCharset}";
		header("Content-Type: {$ct}");
	}
	
	/**
	 * Sets attachment headers for file downloads
	 * @param string $filename Downlaoded file name
	 * @param string $mimeType Attachment MIME type. This parameter is ignored if $addContentType is not set.
	 * @param string $charset Attachment charset. If NULL, no charset is provided. This parameter is ignored if $addContentType is not set. (Defaults to UTF-8)
	 * @param bool $addContentType If set, the function will add the Content-Type header. (Defaults to true)
	 */
	public static function setAttachmentHeaders($filename, $mimeType, $charset = 'UTF-8', $addContentType = true) {
		if (!empty($addContentType))
		{
			if ($charset !== NULL && is_string($charset))
				header("Content-Type: {$mimeType}; charset={$charset}");
			else 
				header("Content-Type: {$mimeType}");
		}
		header("Content-Disposition: attachment; filename=\"{$filename}\"");
	}
	
	/**
	 * Flushes all output buffers
	 */
	public static function flushAllOutputBuffers() {
		$c = ob_get_level();
		for ($i = 0; $i < $c; $i++)
			ob_end_flush();
	}
	
	/**
	 * Uncaught exception handler
	 * @param \Throwable $exception Uncaught exception
	 */
	public static function exceptionHandler(\Throwable $exception) {
		$message = sprintf("Uncaught %s: %s\n%s", get_class($exception), $exception->getMessage(), $exception->getTraceAsString());
		self::dieWithStatusCode(400, $message);
	}
}

set_exception_handler(array(CoreManager::class, 'exceptionHandler'));