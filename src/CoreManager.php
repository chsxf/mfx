<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\Attributes\AnonymousRoute;
use chsxf\MFX\Attributes\ContentType;
use chsxf\MFX\Attributes\PreRouteCallback;
use chsxf\MFX\Attributes\PostRouteCallback;
use chsxf\MFX\Attributes\RedirectURL;
use chsxf\MFX\Attributes\RequiredContentType;
use chsxf\MFX\Attributes\RequiredRequestMethod;
use chsxf\MFX\Attributes\Template;
use chsxf\MFX\DataValidator\Twig\Extension;
use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\Routers\IRouter;
use chsxf\MFX\Routers\PathRouter;
use chsxf\MFX\Routers\RouteAttributesParser;
use chsxf\MFX\Services\IAuthenticationService;
use chsxf\MFX\Services\IConfigService;
use chsxf\MFX\Services\ICoreServiceProvider;
use chsxf\MFX\Services\IDatabaseService;
use chsxf\MFX\Services\ILocalizationService;
use chsxf\MFX\Services\IProfilingService;
use chsxf\MFX\Services\IRequestService;
use chsxf\MFX\Services\ISessionService;
use chsxf\MFX\Services\ITemplateService;
use chsxf\MFX\Services\Proxies\CoreServiceProviderProxy;
use chsxf\Twig\Extension\Gettext;
use chsxf\Twig\Extension\Lazy;
use chsxf\Twig\Extension\SwitchCase;
use ReflectionClass;
use ReflectionException;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Wikimedia\Minify\CSSMin;
use Wikimedia\Minify\JavaScriptMinifier;

/**
 * Core manager class
 *
 * Handles requests and responses.
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 *
 */
final class CoreManager implements IRequestService, ITemplateService
{
    public const HTML_CONTENT_TYPE = 'text/html';

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

    private ?Scripts $scripts = null;
    private ?StyleSheets $styleSheets = null;

    private ICoreServiceProvider $coreServiceProvider;

    /**
     * Constructor
     * @since 2.0
     * @param ErrorManager $errorManager Error manager instance
     * @param IConfigService $configService Config service instance
     * @param ILocalizationService $localizationService Localization service instance
     * @param IProfilingService $profilingService Profiling service instance
     * @param IAuthenticationService $authenticationService Authentication service instance
     * @param IDatabaseService $databaseService Database service instance
     * @param ISessionService $sessionService Session service instance
     */
    public function __construct(
        private readonly ErrorManager $errorManager,
        private readonly IConfigService $configService,
        private readonly ILocalizationService $localizationService,
        private readonly IProfilingService $profilingService,
        private readonly IAuthenticationService $authenticationService,
        private readonly IDatabaseService $databaseService,
        private readonly ISessionService $sessionService
    ) {
        // Fake protocols
        $this->fakeProtocols = array(
            'mfxjs' => ROOT . '/static/js/',
            'mfxcss' => ROOT . '/static/css/'
        );
        $fakeProtocolsFromConfig = $configService->getValue(ConfigConstants::FAKE_PROTOCOLS, array());
        if (is_array($fakeProtocolsFromConfig)) {
            $definedWrappers = stream_get_wrappers();
            foreach ($fakeProtocolsFromConfig as $k => $v) {
                if (in_array($k, $definedWrappers) || array_key_exists($k, $this->fakeProtocols) || !preg_match('/^\w+$/', $k)) {
                    continue;
                }

                // Trailing with a back slash
                if (!preg_match('#/$#', $v) && $v != '') {
                    $v .= '/';
                }

                $this->fakeProtocols[$k] = $v;
            }
        }

        $this->initializeScriptsAndStylsheets();

        $this->coreServiceProvider = new CoreServiceProviderProxy($configService, $this, $this, $localizationService, $profilingService, $this->scripts, $this->styleSheets, $authenticationService, $databaseService, $sessionService);
    }

    /**
     * @since 2.0
     */
    private function initializeScriptsAndStylsheets()
    {
        if ($this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, self::HTML_CONTENT_TYPE) == self::HTML_CONTENT_TYPE) {
            // Adding scripts
            $this->scripts = new Scripts($this);
            $this->scripts->add('https://code.jquery.com/jquery-3.7.1.slim.min.js');
            $this->scripts->add('mfxjs://layout.min.js');
            $this->scripts->add('mfxjs://ui.min.js');
            $this->scripts->add('mfxjs://mainObserver.min.js');
            $this->scripts->add('mfxjs://string.min.js');
            if ($this->profilingService->isActive()) {
                $this->scripts->add('https://www.google.com/jsapi');
            }
            $this->initializeUserScripts();

            // Adding stylesheets
            $this->styleSheets = new StyleSheets($this);
            $this->styleSheets->add('mfxcss://framework.min.css');
            $this->initializeUserStyleSheets();
        }
    }

    /**
     * @since 2.0
     */
    private function initializeUserScripts()
    {
        $userScripts = $this->configService->getValue(ConfigConstants::SCRIPTS, array());
        if (is_array($userScripts)) {
            foreach ($userScripts as $script) {
                if (is_string($script)) {
                    $this->scripts->add($script);
                } else if (is_array($script)) {
                    $args = [
                        'url' => '',
                        'inline' => false,
                        'prepend' => false,
                        'type' => Scripts::DEFAULT_TYPE,
                        'extras' => []
                    ];
                    foreach ($script as $key => $value) {
                        if ($key != 'extras' && array_key_exists($key, $args)) {
                            $args[$key] = $value;
                        } else {
                            $args['extras'][$key] = $value;
                        }
                    }

                    $this->scripts->add($args['url'], $args['inline'], $args['prepend'], $args['type'], $args['extras']);
                }
            }
        }
    }

    /**
     * @since 2.0
     */
    private function initializeUserStyleSheets()
    {
        $userSheets = $this->configService->getValue(ConfigConstants::STYLESHEETS, array());
        if (is_array($userSheets)) {
            foreach ($userSheets as $sheet) {
                if (is_string($sheet)) {
                    $this->styleSheets->add($sheet);
                } else if (is_array($sheet)) {
                    $args = [
                        'url' => '',
                        'media' => StyleSheets::DEFAULT_MEDIA,
                        'inline' => false,
                        'prepend' => false,
                        'type' => StyleSheets::DEFAULT_TYPE,
                        'extras' => []
                    ];
                    foreach ($sheet as $key => $value) {
                        if ($key != 'extras' && array_key_exists($key, $args)) {
                            $args[$key] = $value;
                        } else {
                            $args['extras'][$key] = $value;
                        }
                    }

                    $this->styleSheets->add($args['url'], $args['media'], $args['inline'], $args['prepend'], $args['type'], $args['extras']);
                }
            }
        }
    }

    /**
     * Converts the fake protocols in the input strings
     *
     * @since 2.0
     * @param string $str Input string
     * @return string
     */
    public function convertFakeProtocols(string $str): string
    {
        $search = array();
        foreach (array_keys($this->fakeProtocols) as $k) {
            $search[] = "{$k}://";
        }
        return str_replace($search, array_values($this->fakeProtocols), $str);
    }

    /**
     * Gets the Twig environment for the current request
     *
     * @since 2.0
     * @return \Twig\Environment
     */
    public function getTwig(): ?Environment
    {
        if ($this->currentTwigEnvironment === null) {
            $this->profilingService->pushEvent('Loading Twig');
            $fsLoader = new FilesystemLoader($this->configService->getValue(ConfigConstants::TWIG_TEMPLATES, array()));
            $fsLoader->addPath(ROOT . '/templates', 'mfx');
            $twig = new Environment($fsLoader, [
                'cache' => $this->configService->getValue(ConfigConstants::TWIG_CACHE, '../tmp/twig_cache'),
                'debug' => true,
                'strict_variables' => true,
                'autoescape' => false
            ]);
            $twig->addExtension(new DebugExtension());
            $twig->addExtension(new Lazy());
            $twig->addExtension(new Gettext());
            $twig->addExtension(new SwitchCase());
            $twig->addExtension(new Extension());
            $customTwigExtensions = $this->configService->getValue(ConfigConstants::TWIG_EXTENSIONS, array());
            foreach ($customTwigExtensions as $ext) {
                $twig->addExtension(new $ext());
            }

            $this->currentTwigEnvironment = $twig;
            $this->profilingService->pushEvent('Twig Loaded');
        }
        return $this->currentTwigEnvironment;
    }

    /**
     * Handles the request sent to the server
     *
     * @ignore
     * @since 2.0
     * @param string $defaultRoute Route to use if none can be guessed from request
     */
    public function handleRequest(string $requestURI, string $defaultRoute)
    {
        // Finding route path info from REQUEST_URI
        $prefix = $this->configService->getValue(ConfigConstants::REQUEST_PREFIX, '');
        if (!preg_match('#/$#', $prefix)) {
            $prefix .= '/';
        }
        if (strlen($requestURI) < strlen($prefix)) {
            throw new MFXException(message: 'Invalid request URI');
        }
        $routePathInfo = substr($requestURI, strlen($prefix));
        $routePathInfo = explode('?', $routePathInfo, 2);
        $routePathInfo = ltrim($routePathInfo[0], '/');

        // Parsing through the router
        $routerClassName = $this->configService->getValue(ConfigConstants::ROUTER_CLASS, PathRouter::class);
        try {
            $routerClass = new ReflectionClass($routerClassName);
        } catch (ReflectionException $e) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Invalid router class '{$routerClassName}'");
        }
        if (!$routerClass->isSubclassOf(IRouter::class)) {
            throw new MFXException(HttpStatusCodes::internalServerError, "Invalid router class '{$routerClassName}'");
        }
        $router = $routerClass->newInstance();
        $routerData = $router->parseRoute($this->coreServiceProvider, $routePathInfo, $defaultRoute);

        // Checking pre-conditions
        // -- Anonymous route
        $isAnonymous = $routerData->routeAttributes->hasAttribute(AnonymousRoute::class) || $routerData->routeProviderAttributes->hasAttribute(AnonymousRoute::class);
        if (!$isAnonymous && $this->authenticationService->isEnabled() && !$this->authenticationService->hasAuthenticatedUser()) {
            throw new MFXException(HttpStatusCodes::forbidden);
        }
        // -- Request method
        if ($routerData->routeAttributes->hasAttribute(RequiredRequestMethod::class) && !$routerData->routeAttributes->hasAttributeWithValue(RequiredRequestMethod::class, $this->getRequestMethod()->value)) {
            throw new MFXException(HttpStatusCodes::methodNotAllowed);
        }
        // -- Content-Type
        if ($routerData->routeAttributes->hasAttribute(RequiredContentType::class) && !$routerData->routeAttributes->hasAttributeWithValue(RequiredContentType::class, $this->getRequestContentType())) {
            throw new MFXException(HttpStatusCodes::unsupportedMediaType);
        }

        ob_start($this->convertFakeProtocols(...));

        // Pre-processing callbacks
        $this->profilingService->pushEvent('Calling pre-route callbacks');
        $reqResult = null;
        // -- Global
        $callback = $this->configService->getValue(ConfigConstants::REQUEST_PRE_ROUTE_CALLBACK);
        if (!empty($callback) && is_callable($callback)) {
            $reqResult = call_user_func($callback, $this->coreServiceProvider, $routerData);
        }
        // -- Route Provider
        if ($reqResult === null) {
            $callback = $routerData->routeProviderAttributes->getAttributeValue(PreRouteCallback::class);
            if (!empty($callback) && is_callable($callback)) {
                $reqResult = call_user_func($callback, $this->coreServiceProvider, $routerData);
            }
        }
        // -- Route
        if ($reqResult === null) {
            $callback = $routerData->routeAttributes->getAttributeValue(PreRouteCallback::class);
            if (!empty($callback) && is_callable($callback)) {
                $reqResult = call_user_func($callback, $this->coreServiceProvider, $routerData);
            }
        }
        $this->profilingService->pushEvent('Pre-route callbacks completed');

        // Processing route
        $this->profilingService->pushEvent('Building response');
        if ($reqResult === null) {
            $reqResult = $routerData->getResult();
        }
        switch ($reqResult->type()) {
                // Views
            case RequestResultType::VIEW:
                if (!in_array($reqResult->statusCode(), [HttpStatusCodes::ok, HttpStatusCodes::created, HttpStatusCodes::accepted])) {
                    $this->dieWithStatusCode($reqResult->statusCode(), $reqResult->statusCode()->getStatusMessage());
                }

                $this->setResponseContentType($routerData->routeAttributes, $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, 'text/html'), $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
                $routeProvidedTemplate = $routerData->routeAttributes->hasAttribute(Template::class) ? $routerData->routeAttributes->getAttributeValue(Template::class) : null;
                $template = $reqResult->template(($routeProvidedTemplate === null) ? $routerData->defaultTemplate : $routeProvidedTemplate);

                $context = array_merge(RequestResult::getViewGlobals(), $reqResult->data(), array(
                    'mfx_scripts' => $this->scripts->export(),
                    'mfx_stylesheets' => $this->styleSheets->export(),
                    'mfx_root_url' => $this->getRootURL(),
                    'mfx_errors_and_notifs' => $this->errorManager->flush($this->getTwig()),
                    'mfx_current_user' => $this->authenticationService->getCurrentAuthenticatedUser(),
                    'mfx_locale' => $this->localizationService->getLocale(),
                    'mfx_language' => $this->localizationService->getLanguage()
                ));

                $this->getTwig()->display($template, $context);
                break;

                // Edit requests - Mostly requests with POST data
            case RequestResultType::REDIRECT:
                $redirectionURL = $reqResult->redirectURL();
                if (empty($redirectionURL) && $routerData->routeAttributes->hasAttribute(RedirectURL::class)) {
                    $redirectionURL = $routerData->routeAttributes->getAttributeValue(RedirectURL::class);
                }
                $this->redirect($redirectionURL);
                break;

                // Asynchronous requests expecting JSON data
            case RequestResultType::JSON:
                $this->outputJSON($reqResult, $routerData->routeAttributes, $this->getTwig());
                break;

                // Asynchronous requests expecting XML data
            case RequestResultType::XML:
                $this->outputXML($reqResult, $routerData->routeAttributes, $this->getTwig());
                break;

                // Status
            case RequestResultType::STATUS:
                $this->outputStatusCode($reqResult->statusCode(), $reqResult->data());
                break;
        }
        $this->profilingService->pushEvent('Response built');

        // Post-processing callback
        $this->profilingService->pushEvent('Calling post-route callbacks');
        // -- Starting output buffering to prevent unvolontury output during post-processing callback
        ob_start();
        // -- Route
        $callback = $routerData->routeAttributes->getAttributeValue(PostRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $this->coreServiceProvider, $routerData);
        }
        // -- Route provider
        $callback = $routerData->routeProviderAttributes->getAttributeValue(PostRouteCallback::class);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $this->coreServiceProvider, $routerData);
        }
        // -- Global
        $callback = $this->configService->getValue(ConfigConstants::REQUEST_POST_ROUTE_CALLBACK);
        if (!empty($callback) && is_callable($callback)) {
            call_user_func($callback, $this->coreServiceProvider, $routerData);
        }
        // -- Discarding output if any
        ob_end_clean();
        $this->profilingService->pushEvent('Post-route callbacks completed');
    }

    /**
     * @since 2.0
     */
    private function outputJSON(RequestResult $reqResult, ?RouteAttributesParser $routeAttributes = null, Environment $twig = null)
    {
        $this->setStatusCode($reqResult->statusCode());
        $this->setResponseContentType($routeAttributes, 'application/json', $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
        if ($twig != null && $reqResult->preformatted()) {
            $this->errorManager->flush();
            $template = $twig->createTemplate($reqResult->data());
            echo $template->render(array('mfx_current_user' => $this->authenticationService->getCurrentAuthenticatedUser()));
        } else {
            $d = $reqResult->data();
            $this->errorManager->flushToArrayOrObject($d);
            echo JSONTools::filterAndEncode($d);
        }
    }

    /**
     * @since 2.0
     */
    private function outputXML(RequestResult $reqResult, ?RouteAttributesParser $routeAttributes = null, Environment $twig = null)
    {
        $this->setStatusCode($reqResult->statusCode());
        $this->setResponseContentType($routeAttributes, 'application/xml', $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8'));
        if ($twig != null && $reqResult->preformatted()) {
            $this->errorManager->flush();
            $template = $twig->createTemplate($reqResult->data());
            echo $template->render(array('mfx_current_user' => $this->authenticationService->getCurrentAuthenticatedUser()));
        } else {
            $d = $reqResult->data();
            $this->errorManager->flushToArrayOrObject($d);
            echo XMLTools::build($reqResult->data());
        }
    }

    /**
     * Builds the root URL from server information (protocol, host and PHP_SELF)
     *
     * @since 2.0
     * @return string
     */
    public function getRootURL(): string
    {
        if (null === $this->rootURL) {
            $this->rootURL = $this->configService->getValue(ConfigConstants::BASE_HREF, null);
            if (null === $this->rootURL) {
                if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
                } else {
                    $protocol = (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http' : 'https';
                }
                $this->rootURL = "{$protocol}://{$_SERVER['HTTP_HOST']}" . preg_replace('#/mfx$#', '/', dirname($_SERVER['PHP_SELF']));
                if (!preg_match('#/$#', $this->rootURL)) {
                    $this->rootURL .= '/';
                }
            }
        }
        return $this->rootURL;
    }

    /**
     * Get the method used by the request
     * (ex: GET, POST...)
     * @since 2.0
     * @return RequestMethod
     */
    public function getRequestMethod(): RequestMethod
    {
        return RequestMethod::from($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Get the content-type used by the request
     * (ex: application/json)
     * @since 2.0.1
     * @return null|string
     */
    public function getRequestContentType(): ?string
    {
        $regs = array();
        if (preg_match('/^([^;]+);?/', $_SERVER['CONTENT_TYPE'], $regs)) {
            return $regs[1];
        }
        return null;
    }

    /**
     * Redirects the user to the specified URL, the HTTP referer if defined and same host, or the website root
     *
     * @since 2.0
     * @param string $redirectURL Target redirection URL (Defaults to NULL)
     */
    private function redirect(string $redirectURL = null): never
    {
        if (empty($redirectURL) && !empty($_SERVER['HTTP_REFERER']) && preg_match("#https?://{$_SERVER['HTTP_HOST']}#", $_SERVER['HTTP_REFERER'])) {
            $redirectURL = $_SERVER['HTTP_REFERER'];
        }

        if (empty($redirectURL) || !preg_match('#^https?://#', $redirectURL)) {
            // Building URL
            $r = $this->getRootURL();
            if (!empty($redirectURL)) {
                $r .= ltrim($redirectURL, '/');
            }
        } else {
            $r = $redirectURL;
        }
        header("Location: $r");
        $this->errorManager->freeze();
        exit();
    }

    /**
     * Sets the HTTP status code
     * @since 2.0
     * @param HttpStatusCodes $code HTTP status code to emit (Defaults to 200 OK)
     */
    private function setStatusCode(HttpStatusCodes $code = HttpStatusCodes::ok)
    {
        header(sprintf("HTTP/1.1 %d %s", $code->value, $code->getStatusMessage()));
    }

    /**
     * Emits a HTTP status code
     *
     * @since 2.0
     * @param HttpStatusCodes $code HTTP status code to emit (Defaults to 400 Bad Request)
     * @param ?string $message Custom message to output with status code
     */
    private function outputStatusCode(HttpStatusCodes $code = HttpStatusCodes::badRequest, ?string $message = null)
    {
        $this->setStatusCode($code);

        $contentType = $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CONTENT_TYPE, 'text/plain');
        $charset = $this->configService->getValue(ConfigConstants::RESPONSE_DEFAULT_CHARSET, 'UTF-8');

        $data = [
            'code' => $code->value,
            'status' => $code->getStatusMessage()
        ];
        if (!empty($message)) {
            $data['message'] = $message;
        }

        $this->setResponseContentType(null, $contentType, $charset);
        switch ($contentType) {
            case 'application/json':
                $reqResult = RequestResult::buildJSONRequestResult($data, false, $code);
                $this->outputJSON($reqResult);
                break;
            case 'application/xml':
                $reqResult = RequestResult::buildXMLRequestResult($data, false, $code);
                $this->outputXML($reqResult);
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
     * @since 2.0
     * @param HttpStatusCodes $code HTTP status code to emit (Defaults to 400 Bad Request)
     * @param ?string $message Custom message to output with status code
     */
    private function dieWithStatusCode(HttpStatusCodes $code = HttpStatusCodes::badRequest, ?string $message = null): never
    {
        $this->outputStatusCode($code, $message);
        $this->errorManager->freeze();
        exit();
    }

    /**
     * Sets the response Content-Type header from the route attributes
     *
     * @since 2.0
     * @param RouteAttributesParser $routerData->routeAttributes Attributes of the route
     * @param string $default Content type to use if not provided by the route.
     * @param string $defaultCharset Charset to use if not provided by the route.
     */
    private function setResponseContentType(?RouteAttributesParser $routeAttributes, string $default, string $defaultCharset)
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
     * @since 2.0
     * @param string $filename Downlaoded file name
     * @param string $mimeType Attachment MIME type. This parameter is ignored if $addContentType is not set.
     * @param string $charset Attachment charset. If NULL, no charset is provided. This parameter is ignored if $addContentType is not set. (Defaults to UTF-8)
     * @param bool $addContentType If set, the function will add the Content-Type header. (Defaults to true)
     */
    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true): void
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
     * Uncaught exception handler
     *
     * @since 2.0
     * @ignore
     * @param \Throwable $exception Uncaught exception
     */
    public function exceptionHandler(\Throwable $exception)
    {
        $code = ($exception instanceof MFXException) ? $exception->getHttpCode() : HttpStatusCodes::badRequest;
        $message = null;
        if (!empty($this->configService->getValue(ConfigConstants::RESPONSE_FULL_ERRORS, false))) {
            $message = sprintf("Uncaught %s: %s\n%s", get_class($exception), $exception->getMessage(), $exception->getTraceAsString());
        }
        $this->dieWithStatusCode($code, $message);
    }

    /**
     * @since 1.0.1
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
