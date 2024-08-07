<?php

/**
 * Core manager
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\MFX\Attributes\ContentType;
use chsxf\MFX\Attributes\PreRouteCallback;
use chsxf\MFX\Attributes\PostRouteCallback;
use chsxf\MFX\Attributes\RedirectURL;
use chsxf\MFX\Attributes\RequiredContentType;
use chsxf\MFX\Attributes\RequiredRequestMethod;
use chsxf\MFX\Attributes\RouteAttributesParser;
use chsxf\MFX\Attributes\Template;
use chsxf\MFX\L10n\L10nManager;
use chsxf\MFX\Routers\IRouter;
use chsxf\MFX\Routers\PathRouter;
use Twig\Environment;
use Wikimedia\Minify\CSSMin;
use Wikimedia\Minify\JavaScriptMinifier;

/**
 * Core manager singleton class
 *
 * Handles all requests and responses.
 *
 * @since 1.0
 */
final class CoreManager
{
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
    private static ?CoreManager $singleInstance = null;

    /**
     * @var array Fake protocols list (keys are protocols names and values replacement strings)
     */
    private array $fakeProtocols = array();

    /**
     * @var string Root URL container (as built from server information)
     */
    private ?string $rootURL = null;

    /**
     * @var \Twig\Environment Twig environment for the current request
     */
    private ?Environment $currentTwigEnvironment = null;

    /**
     * Ensures the singleton class instance has been correctly initialized only once
     * @return CoreManager the singleton class instance
     */
    private static function ensureInit()
    {
        if (self::$singleInstance === null) {
            self::$singleInstance = new CoreManager();

            // Fake protocols
            $mfxRelativeBaseHREF = Config::get(ConfigConstants::RELATIVE_BASE_HREF, 'vendor/chsxf/mfx/static');
            if ('/' !== $mfxRelativeBaseHREF) {
                $mfxRelativeBaseHREF = rtrim($mfxRelativeBaseHREF, '/');
            }
            self::$singleInstance->fakeProtocols = array(
                'mfxjs' => ROOT . '/static/js/',
                'mfxcss' => ROOT . '/static/css/',
                'mfximg' => ROOT . '/static/img/'
            );
            $fakeProtocols = Config::get(ConfigConstants::FAKE_PROTOCOLS, array());
            if (is_array($fakeProtocols)) {
                $definedWrappers = stream_get_wrappers();
                foreach ($fakeProtocols as $k => $v) {
                    if (in_array($k, $definedWrappers) || array_key_exists($k, self::$singleInstance->fakeProtocols) || !preg_match('/^\w+$/', $k)) {
                        continue;
                    }

                    // Trailing with a back slash
                    if (!preg_match('#/$#', $v) && $v != '') {
                        $v .= '/';
                    }

                    self::$singleInstance->fakeProtocols[$k] = $v;
                }
            }
            ob_start(array(__CLASS__, 'convertFakeProtocols'));

            if (Config::get(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, 'text/html') == 'text/html') {
                // Adding scripts
                Scripts::add('https://code.jquery.com/jquery-3.7.1.slim.min.js');
                Scripts::add('mfxjs://layout.min.js');
                Scripts::add('mfxjs://ui.min.js');
                Scripts::add('mfxjs://mainObserver.min.js');
                Scripts::add('mfxjs://string.min.js');
                $userScripts = Config::get(ConfigConstants::SCRIPTS, array());
                if (is_array($userScripts)) {
                    foreach ($userScripts as $s) {
                        Scripts::add($s);
                    }
                }

                // Adding stylesheets
                StyleSheets::add('mfxcss://framework.min.css');
                $userSheets = Config::get(ConfigConstants::STYLESHEETS, array());
                if (is_array($userSheets)) {
                    foreach ($userSheets as $s) {
                        StyleSheets::add($s);
                    }
                }
            }
        }
        return self::$singleInstance;
    }

    /**
     * Converts the fake protocols in the input strings
     *
     * @since 1.0
     *
     * @param string $str Input string
     *
     * @return string
     */
    public static function convertFakeProtocols(string $str): string
    {
        $inst = self::ensureInit();
        $search = array();
        foreach (array_keys($inst->fakeProtocols) as $k) {
            $search[] = "{$k}://";
        }
        return str_replace($search, array_values($inst->fakeProtocols), $str);
    }

    /**
     * Gets the Twig environment for the current request
     *
     * @since 1.0
     *
     * @return \Twig\Environment
     */
    public static function getTwig(): ?Environment
    {
        return self::ensureInit()->currentTwigEnvironment;
    }

    /**
     * Handles the request sent to the server
     *
     * @ignore
     *
     * @param string $defaultRoute Route to use if none can be guessed from request
     */
    public static function handleRequest(Environment $twig, string $defaultRoute)
    {
        $inst = self::ensureInit();

        $inst->currentTwigEnvironment = $twig;

        if (CommandLine::isCLI()) {
            $routePathInfo = $_SERVER['REQUEST_URI'];
            $lastForwardSlashIndex = strrpos($routePathInfo, '/');
            if ($lastForwardSlashIndex !== false) {
                $routePathInfo = substr($routePathInfo, $lastForwardSlashIndex + 1);
            }
        } else {
            // Finding route path info from REQUEST_URI
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            if (PHP_OS_FAMILY == 'Windows' && $scriptPath == '\\') {
                $scriptPath = '/';
            }
            $prefix = preg_replace('#/mfx$#', '/', $scriptPath);
            if (!preg_match('#/$#', $prefix)) {
                $prefix .= '/';
            }
            $prefix .= Config::get(ConfigConstants::REQUEST_PREFIX, '');
            if (!preg_match('#/$#', $prefix)) {
                $prefix .= '/';
            }
            $routePathInfo = substr($_SERVER['REQUEST_URI'], strlen($prefix));
            $routePathInfo = explode('?', $routePathInfo, 2);
            $routePathInfo = ltrim($routePathInfo[0], '/');
        }

        // Parsing through the router
        $routerClass = Config::get(ConfigConstants::ROUTER_CLASS, PathRouter::class);
        if (!class_exists($routerClass) || !is_subclass_of($routerClass, IRouter::class)) {
            throw new \ErrorException("Invalid router class '{$routerClass}'");
        }
        $router = new $routerClass();
        if ($router instanceof IRouter) {
            $routerData = $router->parseRoute($routePathInfo, $defaultRoute);
        }

        // Checking pre-conditions
        // -- Request method
        $requiredRequestMethod = $routerData->routeAttributes->getAttributeValue(RequiredRequestMethod::class);
        if (!empty($requiredRequestMethod) && $_SERVER['REQUEST_METHOD'] !== $requiredRequestMethod) {
            self::dieWithStatusCode(405);
        }
        // -- Content-Type
        $requiredContentType = $routerData->routeAttributes->getAttributeValue(RequiredContentType::class);
        if (!empty($requiredContentType)) {
            $regs = array();
            preg_match('/^([^;]+);?/', $_SERVER['CONTENT_TYPE'], $regs);
            if ($regs[1] !== $requiredContentType) {
                self::dieWithStatusCode(415);
            }
        }

        // Pre-processing callbacks
        // -- Global
        $callback = Config::get(ConfigConstants::REQUEST_PRE_ROUTE_CALLBACK);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }
        // -- Route Provider
        $callback = $routerData->routeProviderAttributes->getAttributeValue(PreRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }
        // -- Route
        $callback = $routerData->routeAttributes->getAttributeValue(PreRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }

        // Processing route
        $reqResult = $routerData->routeMethod->invoke(null, $routerData->routeParams);
        $routeProvidedTemplate = $routerData->routeAttributes->hasAttribute(Template::class) ? $routerData->routeAttributes->getAttributeValue(Template::class) : null;
        switch ($reqResult->type()) {
                // Views
            case RequestResultType::VIEW:
                if (!in_array($reqResult->statusCode(), [200, 201, 206])) {
                    self::dieWithStatusCode($reqResult->statusCode());
                }

                CoreProfiler::pushEvent('Building response');
                self::setResponseContentType($routerData->routeAttributes, Config::get(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, 'text/html'), Config::get(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
                $template = $reqResult->template(($routeProvidedTemplate === null) ? $routerData->defaultTemplate : $routeProvidedTemplate);

                $context = array_merge(RequestResult::getViewGlobals(), $reqResult->data(), array(
                    'mfx_scripts' => Scripts::export($twig),
                    'mfx_stylesheets' => StyleSheets::export($twig),
                    'mfx_root_url' => self::getRootURL(),
                    'mfx_errors_and_notifs' => ErrorManager::flush($twig),
                    'mfx_current_user' => User::currentUser(),
                    'mfx_locale' => L10nManager::getLocale(),
                    'mfx_language' => L10nManager::getLanguage()
                ));

                $twig->display($template, $context);
                CoreProfiler::pushEvent('Response built');
                break;

                // Edit requests - Mostly requests with POST data
            case RequestResultType::REDIRECT:
                $redirectionURL = $reqResult->redirectURL();
                if (empty($redirectionURL) && $routerData->routeAttributes->hasAttribute(RedirectURL::class)) {
                    $redirectionURL = $routerData->routeAttributes->getAttributeValue(RedirectURL::class);
                }
                self::redirect($redirectionURL);
                break;

                // Asynchronous requests expecting JSON data
            case RequestResultType::JSON:
                self::outputJSON($reqResult, $routerData->routeAttributes, $twig);
                break;

                // Asynchronous requests expecting XML data
            case RequestResultType::XML:
                self::outputXML($reqResult, $routerData->routeAttributes, $twig);
                break;

                // Status
            case RequestResultType::STATUS:
                self::outputStatusCode($reqResult->statusCode(), $reqResult->data());
                break;
        }

        // Post-processing callback
        // -- Starting output buffering to prevent unvolontury output during post-processing callback
        ob_start();
        // -- Route
        $callback = $routerData->routeAttributes->getAttributeValue(PostRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }
        // -- Route provider
        $callback = $routerData->routeProviderAttributes->getAttributeValue(PostRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }
        // -- Global
        $callback = Config::get(ConfigConstants::REQUEST_POST_ROUTE_CALLBACK);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $routerData);
        }
        // -- Discarding output if any
        ob_end_clean();
    }

    private static function outputJSON(RequestResult $reqResult, ?RouteAttributesParser $routeAttributes = null, Environment $twig = null)
    {
        self::setStatusCode($reqResult->statusCode());
        self::setResponseContentType($routeAttributes, 'application/json', Config::get(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
        if ($twig != null && $reqResult->preformatted()) {
            ErrorManager::flush();
            $template = $twig->createTemplate($reqResult->data());
            echo $template->render(array('mfx_current_user' => User::currentUser()));
        } else {
            $d = $reqResult->data();
            ErrorManager::flushToArrayOrObject($d);
            echo JSONTools::filterAndEncode($d);
        }
    }

    private static function outputXML(RequestResult $reqResult, ?RouteAttributesParser $routeAttributes = null, Environment $twig = null)
    {
        self::setStatusCode($reqResult->statusCode());
        self::setResponseContentType($routeAttributes, 'application/xml', Config::get(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
        if ($twig != null && $reqResult->preformatted()) {
            ErrorManager::flush();
            $template = $twig->createTemplate($reqResult->data());
            echo $template->render(array('mfx_current_user' => User::currentUser()));
        } else {
            $d = $reqResult->data();
            ErrorManager::flushToArrayOrObject($d);
            echo XMLTools::build($reqResult->data());
        }
    }

    /**
     * Builds the root URL from server information (protocol, host and PHP_SELF)
     *
     * @since 1.0
     *
     * @return string
     */
    public static function getRootURL(): string
    {
        $inst = self::ensureInit();
        if (null === $inst->rootURL) {
            $inst->rootURL = Config::get(ConfigConstants::BASE_HREF, null);
            if (null === $inst->rootURL) {
                if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
                } else {
                    $protocol = (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http' : 'https';
                }
                $inst->rootURL = "{$protocol}://{$_SERVER['HTTP_HOST']}" . preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
                if (!preg_match('#/$#', $inst->rootURL)) {
                    $inst->rootURL .= '/';
                }
            }
        }
        return $inst->rootURL;
    }

    /**
     * Redirects the user the specified URL, the HTTP referer if defined and same host or the website root
     *
     * @since 1.0
     *
     * @param string $redirectURL Target redirection URL (Defaults to NULL)
     */
    public static function redirect(string $redirectURL = null)
    {
        if (empty($redirectURL) && !empty($_SERVER['HTTP_REFERER']) && preg_match("#https?://{$_SERVER['HTTP_HOST']}#", $_SERVER['HTTP_REFERER'])) {
            $redirectURL = $_SERVER['HTTP_REFERER'];
        }

        if (empty($redirectURL) || !preg_match('#^https?://#', $redirectURL)) {
            // Building URL
            $r = self::getRootURL();
            if (!empty($redirectURL)) {
                $r .= ltrim($redirectURL, '/');
            }
        } else {
            $r = $redirectURL;
        }
        header("Location: $r");
        ErrorManager::freeze();
    }

    /**
     * Sets the HTTP status code
     * @param int $code HTTP status code to emit (Defaults to 200 OK)
     * @return int the specified status code or 400 if invalid
     */
    private static function setStatusCode(int $code = 200): int
    {
        if (!array_key_exists($code, self::$HTTP_STATUS_CODES)) {
            $code = 400;
        }
        header(sprintf("HTTP/1.1 %d %s", $code, self::$HTTP_STATUS_CODES[$code]));
        return $code;
    }

    /**
     * Emits a HTTP status code
     * @param int $code HTTP status code to emit (Defaults to 400 Bad Request)
     * @param ?string $message Custom message to output with status code
     */
    private static function outputStatusCode(int $code = 400, ?string $message = null)
    {
        $code = self::setStatusCode($code);

        $contentType = Config::get(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, 'text/plain');
        $charset = Config::get(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8');

        $data = array(
            'code' => $code,
            'status' => self::$HTTP_STATUS_CODES[$code]
        );
        if (!empty($message)) {
            $data['message'] = $message;
        }

        self::setResponseContentType(null, $contentType, $charset);
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
                if (isset($data['message'])) {
                    echo "\n{$data['message']}";
                }
                break;
        }
    }

    /**
     * Terminates the script and emits a HTTP status code
     *
     * @since 1.0
     *
     * @param int $code HTTP status code to emit (Defaults to 400 Bad Request)
     * @param ?string $message Custom message to output with status code
     */
    public static function dieWithStatusCode(int $code = 400, ?string $message = null)
    {
        self::outputStatusCode($code, $message);
        ErrorManager::freeze();
        exit();
    }

    /**
     * Sets the response Content-Type header from the route attributes
     *
     * @param RouteAttributesParser $routerData->routeAttributes Attributes of the route
     * @param string $default Content type to use if not provided by the route.
     * @param string $defaultCharset Charset to use if not provided by the route.
     */
    private static function setResponseContentType(?RouteAttributesParser $routeAttributes, string $default, string $defaultCharset)
    {
        $ct = ($routeAttributes !== null && $routeAttributes->hasAttribute(ContentType::class)) ? $routeAttributes->getAttributeValue(ContentType::class) : $default;
        if (!preg_match('/;\s+charset=.+$/', $ct)) {
            $ct .= "; charset={$defaultCharset}";
        }
        header("Content-Type: {$ct}");
    }

    /**
     * Sets attachment headers for file downloads
     *
     * @since 1.0
     *
     * @param string $filename Downlaoded file name
     * @param string $mimeType Attachment MIME type. This parameter is ignored if $addContentType is not set.
     * @param string $charset Attachment charset. If NULL, no charset is provided. This parameter is ignored if $addContentType is not set. (Defaults to UTF-8)
     * @param bool $addContentType If set, the function will add the Content-Type header. (Defaults to true)
     */
    public static function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true)
    {
        if (!empty($addContentType)) {
            if ($charset !== null && is_string($charset)) {
                header("Content-Type: {$mimeType}; charset={$charset}");
            } else {
                header("Content-Type: {$mimeType}");
            }
        }
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
    }

    /**
     * Flushes all output buffers
     *
     * @since 1.0
     */
    public static function flushAllOutputBuffers()
    {
        $c = ob_get_level();
        for ($i = 0; $i < $c; $i++) {
            ob_end_flush();
        }
    }

    /**
     * Uncaught exception handler
     *
     * @ignore
     *
     * @param \Throwable $exception Uncaught exception
     */
    public static function exceptionHandler(\Throwable $exception)
    {
        $message = sprintf("Uncaught %s: %s\n%s", get_class($exception), $exception->getMessage(), $exception->getTraceAsString());
        self::dieWithStatusCode(400, $message);
    }

    /**
     * @ignore
     */
    public static function minifyStaticFiles()
    {
        $path = dirname(dirname(__FILE__)) . '/static/js';
        $cssFiles = scandir($path);
        $cssFiles = array_filter($cssFiles, function ($item) {
            return preg_match('/\.js$/', $item) && !preg_match('/\.min\.js$/', $item);
        });
        foreach ($cssFiles as $cssFile) {
            $inputPath = "{$path}/{$cssFile}";
            $fileContents = file_get_contents($inputPath);
            $minified = JavaScriptMinifier::minify($fileContents);
            $minifiedJsFile = preg_replace('/\.js$/', '.min.js', $cssFile);
            $outputPath = "{$path}/{$minifiedJsFile}";
            file_put_contents($outputPath, $minified);
        }

        $path = dirname(dirname(__FILE__)) . '/static/css';
        $cssFiles = scandir($path);
        $cssFiles = array_filter($cssFiles, function ($item) {
            return preg_match('/\.css$/', $item) && !preg_match('/\.min\.css$/', $item);
        });
        foreach ($cssFiles as $cssFile) {
            $inputPath = "{$path}/{$cssFile}";
            $fileContents = file_get_contents($inputPath);
            $minified = CSSMin::minify($fileContents);
            $minifiedJsFile = preg_replace('/\.css$/', '.min.css', $cssFile);
            $outputPath = "{$path}/{$minifiedJsFile}";
            file_put_contents($outputPath, $minified);
        }
    }
}

set_exception_handler(array(CoreManager::class, 'exceptionHandler'));
