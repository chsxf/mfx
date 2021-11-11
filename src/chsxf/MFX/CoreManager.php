<?php
/**
 * Core manager
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\MFX\Attributes\ContentTypeAttribute;
use chsxf\MFX\Attributes\PreRouteCallbackAttribute;
use chsxf\MFX\Attributes\RedirectURIAttribute;
use chsxf\MFX\Attributes\RequiredContentTypeAttribute;
use chsxf\MFX\Attributes\RequiredRequestMethodAttribute;
use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\Attributes\SubRouteAttribute;
use chsxf\MFX\L10n\L10nManager;
use Twig\Environment;
use Twig\Template;

/**
 * Core manager singleton class
 *
 * Handles all requests and responses.
 */
final class CoreManager
{
	const ROUTE_REGEXP = '/^[[:alnum:]_]+\.[[:alnum:]_]+?$/';

	private static array $HTTP_STATUS_CODES = array(
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
	private static ?CoreManager $_singleInstance = NULL;

	/**
	 * @var array Fake protocols list (keys are protocols names and values replacement strings)
	 */
	private array $_fakeProtocols = array();

	/**
	 * @var string Root URI container (as built from server information)
	 */
	private ?string $_rootURI = NULL;

	/**
	 * @var \Twig_Environment Twig environment for the current request
	 */
	private ?Environment $_currentTwigEnvironment = NULL;

	/**
	 * Ensures the singleton class instance has been correctly initialized only once
	 * @return CoreManager the singleton class instance
	 */
	private static function _ensureInit() {
		if (self::$_singleInstance === NULL) {
			self::$_singleInstance = new CoreManager();

			// Fake protocols
			$mfxRelativeBaseHREF = Config::get('mfx_relative_base_href', 'vendor/chsxf/mfx/static');
            if ('/' !== $mfxRelativeBaseHREF) {
                $mfxRelativeBaseHREF = rtrim($mfxRelativeBaseHREF, '/');
            }
			self::$_singleInstance->_fakeProtocols = array(
					'mfxjs' => "{$mfxRelativeBaseHREF}/js/",
					'mfxcss' => "{$mfxRelativeBaseHREF}/css/",
					'mfximg' => "{$mfxRelativeBaseHREF}/img/"
			);
			$fakeProtocols = Config::get('fake_protocols', array());
			if (is_array($fakeProtocols)) {
				$definedWrappers = stream_get_wrappers();
				foreach ($fakeProtocols as $k => $v) {
                    if (in_array($k, $definedWrappers) || array_key_exists($k, self::$_singleInstance->_fakeProtocols) || !preg_match('/^\w+$/', $k)) {
                        continue;
                    }

					// Trailing with a back slash
                    if (!preg_match('#/$#', $v) && $v != '') {
                        $v .= '/';
                    }

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
				if (is_array($userSheets)) {
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
	public static function convertFakeProtocols($str): string {
		$inst = self::_ensureInit();
		$search = array();
        foreach (array_keys($inst->_fakeProtocols) as $k) {
            $search[] = "{$k}://";
        }
		return str_replace($search, array_values($inst->_fakeProtocols), $str);
	}

	/**
	 * Checks if a specific method is a valid sub-route
	 * @param \ReflectionMethod $method Method to inspect
	 * @return RouteAttributesParser|false The route's attributes parser or false in case of an error
	 */
	public static function isMethodValidSubRoute(\ReflectionMethod $method): RouteAttributesParser|false {
		// Checking method
		$params = $method->getParameters();
        if (!$method->isStatic() || !$method->isPublic() || (count($params) >= 1 && !ArrayTools::isParameterArray($params[0]))) {
            return false;
        }
		// Building parameters from doc comment
		$routeParser = new RouteAttributesParser($method);
		if (!$routeParser->hasAttribute(SubRouteAttribute::class)) {
			return false;
		}
		return $routeParser;
	}

	/**
	 * Gets the Twig environment for the current request
	 * @return \Twig_Environment
	 */
	public static function getTwig(): ?Environment {
		return self::_ensureInit()->_currentTwigEnvironment;
	}

	/**
	 * Handles the request sent to the server
	 *
	 * @param string $defaultRoute Route to use if none can be guessed from request
	 */
	public static function handleRequest(Environment $twig, string $defaultRoute) {
		$inst = self::_ensureInit();

		$inst->_currentTwigEnvironment = $twig;

		// Finding route from REQUEST_URI
		$prefix = preg_replace('#/mfx$#', '/', dirname($_SERVER['SCRIPT_NAME']));
        if (!preg_match('#/$#', $prefix)) {
            $prefix .= '/';
        }
		$prefix .= Config::get('request.prefix', '');
        if (!preg_match('#/$#', $prefix)) {
            $prefix .= '/';
        }
		$routePathInfo = substr($_SERVER['REQUEST_URI'], strlen($prefix));
		$routePathInfo = explode('?', $routePathInfo, 2);
		$routePathInfo = ltrim($routePathInfo[0], '/');

		// Guessing route from path info
		if (empty($routePathInfo)) {
            if ($defaultRoute == 'none') {
                self::dieWithStatusCode(200);
            }

			$route = $defaultRoute;
			$routeParams = array();
		}
		else {
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
			$rc = new \ReflectionClass("\\{$mainRoute}");
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
        if (!$rc->implementsInterface(IRouteProvider::class)) {
            throw new \ErrorException("'{$mainRoute}' is not a valid route provider.");
        }
		$routeAttributes = new RouteAttributesParser($rc);

		// Checking subroute
		$rm = $rc->getMethod($subRoute);
		$subRouteAttributes = self::isMethodValidSubRoute($rm);
        if (false === $subRouteAttributes) {
            throw new \ErrorException("'{$subRoute}' is not a valid subroute of the '{$mainRoute}' route.");
        }

		// Pre-processing callbacks
		// -- Global
		$callback = Config::get('request.pre_route_callback');
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $mainRoute, $subRoute, $routeAttributes, $subRouteAttributes, $routeParams);
        }
		// -- Route
		if ($routeAttributes->hasAttribute(PreRouteCallbackAttribute::class)) {
			$callback = $routeAttributes->getAttributeValue(PreRouteCallbackAttribute::class);
            if (!empty($callback) && is_callable($callback)) {
                call_user_func($callback, $mainRoute, $subRoute, $routeAttributes, $subRouteAttributes, $routeParams);
            }
		}

		// Checking pre-conditions
		// -- Request method
		if ($subRouteAttributes->hasAttribute(RequiredRequestMethodAttribute::class)) {
			if ($_SERVER['REQUEST_METHOD'] !== strtoupper($subRouteAttributes->getAttributeValue(RequiredRequestMethodAttribute::class))) {
				self::dieWithStatusCode(405);
			}
		}
		// -- Content-Type
		if ($subRouteAttributes->hasAttribute(RequiredContentTypeAttribute::class)) {
			$regs = array();
			preg_match('/^([^;]+);?/', $_SERVER['CONTENT_TYPE'], $regs);
			if ($regs[1] !== $subRouteAttributes->getAttributeValue(RequiredContentTypeAttribute::class)) {
				self::dieWithStatusCode(415);
			}
		}

		// Processing route
		$reqResult = $rm->invoke(NULL, $routeParams);
		$routeProvidedTemplate = $subRouteAttributes->hasAttribute(Template::class) ? $subRouteAttributes->getAttributeValue(Template::class) : NULL;
		switch ($reqResult->subRouteType()->value()) {
			// Views
			case SubRouteType::VIEW:
                if ($reqResult->statusCode() != 200) {
                    self::dieWithStatusCode($reqResult->statusCode());
                }

				CoreProfiler::pushEvent('Building response');
				self::_setResponseContentType($subRouteAttributes, Config::get('response.default_content_type', 'text/html'), Config::get('response.default_charset', 'UTF-8'));
				$template = $reqResult->template(($routeProvidedTemplate === NULL) ? str_replace(array('_', '.'), '/', $route) : $routeProvidedTemplate);

				$context = array_merge(RequestResult::getViewGlobals(), $reqResult->data(), array(
						'mfx_scripts' => Scripts::export($twig),
						'mfx_stylesheets' => StyleSheets::export($twig),
						'mfx_root_url' => self::getRootURI(),
						'mfx_errors_and_notifs' => ErrorManager::flush($twig),
						'mfx_current_user' => User::currentUser(),
						'mfx_locale' => L10nManager::getLocale(),
						'mfx_language' => L10nManager::getLanguage()
				));

				$twig->display($template, $context);
				CoreProfiler::pushEvent('Response built');
				break;

			// Edit requests - Mostly requests with POST data
			case SubRouteType::REDIRECT:
				$redirectionURI = $reqResult->redirectURI();
                if (empty($redirectionURI) && $subRouteAttributes->hasAttribute(RedirectURIAttribute::class)) {
                    $redirectionURI = $subRouteAttributes->getAttributeValue(RedirectURIAttribute::class);
                }
				self::redirect($redirectionURI);
				break;

			// Asynchronous requests expecting JSON data
			case SubRouteType::JSON:
				self::outputJSON($reqResult, $subRouteAttributes, $twig);
				break;

			// Asynchronous requests expecting XML data
			case SubRouteType::XML:
				self::outputXML($reqResult, $subRouteAttributes, $twig);
				break;

			// Status
			case SubRouteType::STATUS:
				self::outputStatusCode($reqResult->statusCode(), $reqResult->data());
				break;
		}

		// Post-processing callback
		$callback = Config::get('request.post_route_callback');
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $mainRoute, $subRoute, $routeAttributes, $subRouteAttributes);
        }
	}

	private static function outputJSON(RequestResult $reqResult, ?RouteAttributesParser $subRouteAttributes = NULL, Environment $twig = NULL) {
		self::_setStatusCode($reqResult->statusCode());
		self::_setResponseContentType($subRouteAttributes, 'application/json', Config::get('response.default_charset', 'UTF-8'));
		if ($twig != NULL && $reqResult->preformatted()) {
			ErrorManager::flush();
			echo $twig->render($reqResult->data(), array('mfx_current_user' => User::currentUser()));
		}
		else {
			$d = $reqResult->data();
			ErrorManager::flushToArrayOrObject($d);
			echo JSONTools::filterAndEncode($d);
		}
	}

	private static function outputXML(RequestResult $reqResult, ?RouteAttributesParser $subRouteAttributes = NULL, Environment $twig = NULL) {
		self::_setStatusCode($reqResult->statusCode());
		self::_setResponseContentType($subRouteAttributes, 'application/xml', Config::get('response.default_charset', 'UTF-8'));
		if ($twig != NULL && $reqResult->preformatted()) {
			ErrorManager::flush();
			echo $twig->render($reqResult->data(), array('mfx_current_user' => User::currentUser()));
		}
		else {
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
        if (!empty($routeParams) && preg_match('/\.[a-z0-9]+$/i', $routeParams[count($routeParams) - 1])) {
            self::dieWithStatusCode(404);
        }
	}

	/**
	 * Builds the root URI from server information (protocol, host and PHP_SELF)
	 * @return string
	 */
	public static function getRootURI(): string {
		$inst = self::_ensureInit();
		if (NULL === $inst->_rootURI) {
			$inst->_rootURI = Config::get('base_href', false);
			if (false === $inst->_rootURI) {
				if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
					$protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
				}
				else {
					$protocol = (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http' : 'https';
				}
				$inst->_rootURI = "{$protocol}://{$_SERVER['HTTP_HOST']}".preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
                if (!preg_match('#/$#', $inst->_rootURI)) {
                    $inst->_rootURI .= '/';
                }
			}
		}
		return $inst->_rootURI;
	}

	/**
	 * Redirects the user the specified URI, the HTTP referer if defined and same host or the website root
	 * @param string $redirectURI Target redirection URI (Defaults to NULL)
	 */
	public static function redirect(string $redirectURI = NULL)
	{
        if (empty($redirectURI) && !empty($_SERVER['HTTP_REFERER']) && preg_match("#https?://{$_SERVER['HTTP_HOST']}#", $_SERVER['HTTP_REFERER'])) {
            $redirectURI = $_SERVER['HTTP_REFERER'];
        }

		if (empty($redirectURI) || !preg_match('#^https?://#', $redirectURI)) {
			// Building URI
			$r = self::getRootURI();
			if (!empty($redirectURI))
				$r .= ltrim($redirectURI, '/');
		}
		else {
            $r = $redirectURI;
        }
		header("Location: $r");
		ErrorManager::freeze();
		exit();
	}

	/**
	 * Sets the HTTP status code
	 * @param int $code HTTP status code to emit (Defaults to 200 OK)
	 * @return int the specified status code or 400 if invalid
	 */
	private static function _setStatusCode(int $code = 200): int {
        if (!array_key_exists($code, self::$HTTP_STATUS_CODES)) {
            $code = 400;
        }
		header(sprintf("HTTP/1.1 %d %s", $code, self::$HTTP_STATUS_CODES[$code]));
		return $code;
	}

	/**
	 * Emits a HTTP status code
	 * @param int $code HTTP status code to emit (Defaults to 400 Bad Request)
	 * @param string $message Custom message to output with status code
	 */
	public static function outputStatusCode(int $code = 400, string $message = '') {
		$code = self::_setStatusCode($code);

		$contentType = Config::get('response.default_content_type', 'text/plain');
		$charset = Config::get('response.default_charset', 'UTF-8');

		$data = array(
				'code' => $code,
				'status' => self::$HTTP_STATUS_CODES[$code]
		);
        if (!empty($message)) {
            $data['message'] = $message;
        }

		self::_setResponseContentType(NULL, $contentType, $charset);
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
	public static function dieWithStatusCode(int $code = 400, string $message = '') {
		self::outputStatusCode($code, $message);
		ErrorManager::freeze();
		exit();
	}

	/**
	 * Sets the response Content-Type header from the sub-route documentation comment parameters
	 *
	 * @param RouteAttributesParser $subRouteAttributes Documentation comment parameters of the sub-route
	 * @param string $default Content type to use if not provided by the sub-route.
	 * @param string $defaultCharset Charset to use if not provided by the sub-route.
	 */
	private static function _setResponseContentType(?RouteAttributesParser $subRouteAttributes, string $default, string $defaultCharset)
	{
		$ct = ($subRouteAttributes !== NULL && $subRouteAttributes->hasAttribute(ContentTypeAttribute::class)) ? $subRouteAttributes->getAttributeValue(ContentTypeAttribute::class) : $default;
        if (!preg_match('/;\s+charset=.+$/', $ct)) {
            $ct .= "; charset={$defaultCharset}";
        }
		header("Content-Type: {$ct}");
	}

	/**
	 * Sets attachment headers for file downloads
	 * @param string $filename Downlaoded file name
	 * @param string $mimeType Attachment MIME type. This parameter is ignored if $addContentType is not set.
	 * @param string $charset Attachment charset. If NULL, no charset is provided. This parameter is ignored if $addContentType is not set. (Defaults to UTF-8)
	 * @param bool $addContentType If set, the function will add the Content-Type header. (Defaults to true)
	 */
	public static function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true) {
		if (!empty($addContentType)) {
            if ($charset !== null && is_string($charset)) {
                header("Content-Type: {$mimeType}; charset={$charset}");
            }
			else {
                header("Content-Type: {$mimeType}");
            }
		}
		header("Content-Disposition: attachment; filename=\"{$filename}\"");
	}

	/**
	 * Flushes all output buffers
	 */
	public static function flushAllOutputBuffers() {
		$c = ob_get_level();
        for ($i = 0; $i < $c; $i++) {
            ob_end_flush();
        }
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