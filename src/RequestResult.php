<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Class holding data and information about the request response
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class RequestResult
{
    /**
     * @var array Global data to be passed with all VIEW request results
     */
    private static array $viewGlobalData = array();

    /**
     * @var RequestResultType Request result type
     */
    private RequestResultType $type;
    /**
     * @var string Template to use as the response renderer.
     */
    private ?string $template;
    /**
     * @var mixed Response data holder
     */
    private mixed $data;
    /**
     * @var string Redirection target URL container
     */
    private ?string $redirectURL;
    /**
     * @var int HTTP status code of the response
     */
    private HttpStatusCodes $statusCode;
    /**
     * @var boolean Flag indicating if the XML and JSON data are preformatted or not
     */
    private bool $preformatted;

    /**
     * Constructor
     *
     * Some parameters, such as $template, may be automatically defined in the route function attributes
     * but can be overridden through this constructor if needed.
     *
     * @since 2.0
     * @param RequestResultType $type Request result type. If NULL, the type defaults to VIEW. (Defaults to NULL)
     * @param mixed $data Response data. If a view, $data must be an array. (Defaults to NULL)
     * @param string $template Template to use as the response renderer. Don't add the .twig extension. Should be NULL if not a view. (Defaults to NULL)
     * @param string $redirectURL Target URL to which redirect the user (Defaults to NULL)
     * @param HttpStatusCodes $statusCode HTTP status code of the response (Defaults to 200 - OK).
     * @param boolean $preformatted If set, this flag indicates that $data is preformatted for XML and JSON responses. (Defaults to false)
     *
     * @see RequestResultType
     */
    public function __construct(?RequestResultType $type = null, mixed $data = null, ?string $template = null, ?string $redirectURL = null, HttpStatusCodes $statusCode = HttpStatusCodes::ok, bool $preformatted = false)
    {
        $this->type = ($type ?? RequestResultType::VIEW);
        $this->template = $template;
        $this->data = ($this->type === RequestResultType::VIEW && !is_array($data)) ? array() : $data;
        $this->redirectURL = $redirectURL;
        $this->statusCode = $statusCode;
        $this->preformatted = !empty($preformatted);
    }

    /**
     * Gets the request result type
     * @return RequestResultType
     */
    public function type(): RequestResultType
    {
        return $this->type;
    }

    /**
     * Gets the template to use as the response renderer
     * @param string $defaultValue Default template name if none provided. Don't add the .twig extension. (Defaults to NULL)
     * @return string
     */
    public function template(?string $defaultValue = null): string
    {
        return (empty($this->template) ? $defaultValue : $this->template) . '.twig';
    }

    /**
     * Gets the data generated by the response
     * @return mixed
     */
    public function data(): mixed
    {
        return $this->data;
    }

    /**
     * Gets the redirection URL if existing
     * @return string
     */
    public function redirectURL(): ?string
    {
        return $this->redirectURL;
    }

    /**
     * Gets the HTTP status code of the response
     * @since 2.0
     * @return HttpStatusCodes
     */
    public function statusCode(): HttpStatusCodes
    {
        return $this->statusCode;
    }

    /**
     * Tells if the data is preformmated or not for XML and JSON repsonses
     * @return boolean
     */
    public function preformatted(): bool
    {
        return $this->preformatted;
    }

    /**
     * Add a VIEW global value
     * @param string $name Name of the global value
     * @param mixed $value Value
     */
    public static function addViewGlobal(string $name, mixed $value)
    {
        self::$viewGlobalData[$name] = $value;
    }

    /**
     * Remove a VIEW global value
     * @param string $name Name of the global to remove
     */
    public static function removeViewGlobal(string $name)
    {
        unset(self::$viewGlobalData[$name]);
    }

    /**
     * Gets all VIEW global values
     * @return array
     */
    public static function getViewGlobals(): array
    {
        return self::$viewGlobalData;
    }

    /**
     * Helper function to build RequestResult instances for REDIRECT request results
     * @param string $redirectURL Target URL to which redirect the user (Defaults to NULL)
     * @return RequestResult
     */
    public static function buildRedirectRequestResult(?string $redirectURL = null): RequestResult
    {
        return new RequestResult(RequestResultType::REDIRECT, null, null, $redirectURL);
    }

    /**
     * Helper function to build RequestResult instances for erroneous responses, providing the HTTP status code
     * @since 2.0
     * @param HttpStatusCodes $statusCode HTTP status code of the response
     * @param ?string $message Message
     * @return RequestResult
     */
    public static function buildStatusRequestResult(HttpStatusCodes $statusCode = HttpStatusCodes::badRequest, ?string $message = null): RequestResult
    {
        return new RequestResult(RequestResultType::STATUS, $message, null, null, $statusCode, true);
    }

    /**
     * Helper function to build RequestResult instances for JSON responses
     * @since 2.0
     * @param mixed $data JSON data
     * @param bool $preformatted If set, $data contains preformatted JSON data
     * @param HttpStatusCodes $statusCode HTTP status code of the response
     * @return RequestResult
     */
    public static function buildJSONRequestResult(mixed $data, bool $preformatted = false, HttpStatusCodes $statusCode = HttpStatusCodes::ok): RequestResult
    {
        return new RequestResult(RequestResultType::JSON, $data, null, null, $statusCode, $preformatted);
    }

    /**
     * Helper function to build RequestResult instances for XML responses
     * @since 2.0
     * @param mixed $data XML data
     * @param bool $preformatted If set, $data contains preformatted XML data
     * @param HttpStatusCodes $statusCode HTTP status code of the response
     * @return RequestResult
     */
    public static function buildXMLRequestResult(mixed $data, bool $preformatted = false, HttpStatusCodes $statusCode = HttpStatusCodes::ok): RequestResult
    {
        return new RequestResult(RequestResultType::XML, $data, null, null, $statusCode, $preformatted);
    }
}
