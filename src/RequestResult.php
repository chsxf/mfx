<?php

/**
 * Request response data and information structure
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Class holding data and information about the request response
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
    private int $statusCode;
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
     * @since 1.0
     * @param RequestResultType $type Request result type. If NULL, the type defaults to VIEW. (Defaults to NULL)
     * @param mixed $data Response data. If a view, $data must be an array. (Defaults to NULL)
     * @param string $template Template to use as the response renderer. Don't add the .twig extension. Should be NULL if not a view. (Defaults to NULL)
     * @param string $redirectURL Target URL to which redirect the user (Defaults to NULL)
     * @param int $statusCode HTTP status code of the response (Defaults to 200 - OK).
     * @param boolean $preformatted If set, this flag indicates that $data is preformatted for XML and JSON responses. (Defaults to false)
     *
     * @see RequestResultType
     */
    public function __construct(?RequestResultType $type = null, mixed $data = null, ?string $template = null, ?string $redirectURL = null, int $statusCode = 200, bool $preformatted = false)
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
     * @since 1.0
     * @return RequestResultType
     */
    public function type(): RequestResultType
    {
        return $this->type;
    }

    /**
     * Gets the template to use as the response renderer
     * @since 1.0
     * @param string $defaultValue Default template name if none provided. Don't add the .twig extension. (Defaults to NULL)
     * @return string
     */
    public function template(?string $defaultValue = null): string
    {
        return (empty($this->template) ? $defaultValue : $this->template) . '.twig';
    }

    /**
     * Gets the data generated by the response
     * @since 1.0
     * @return mixed
     */
    public function data(): mixed
    {
        return $this->data;
    }

    /**
     * Gets the redirection URL if existing
     * @since 1.0
     * @return string
     */
    public function redirectURL(): ?string
    {
        return $this->redirectURL;
    }

    /**
     * Gets the HTTP status code of the response
     * @since 1.0
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Tells if the data is preformmated or not for XML and JSON repsonses
     * @since 1.0
     * @return boolean
     */
    public function preformatted(): bool
    {
        return $this->preformatted;
    }

    /**
     * Add a VIEW global value
     * @since 1.0
     * @param string $name Name of the global value
     * @param mixed $value Value
     */
    public static function addViewGlobal(string $name, mixed $value)
    {
        self::$viewGlobalData[$name] = $value;
    }

    /**
     * Remove a VIEW global value
     * @since 1.0
     * @param string $name Name of the global to remove
     */
    public static function removeViewGlobal(string $name)
    {
        unset(self::$viewGlobalData[$name]);
    }

    /**
     * Gets all VIEW global values
     * @since 1.0
     * @return array
     */
    public static function getViewGlobals(): array
    {
        return self::$viewGlobalData;
    }

    /**
     * Helper function to build RequestResult instances for REDIRECT request results
     * @since 1.0
     * @param string $redirectURL Target URL to which redirect the user (Defaults to NULL)
     * @return RequestResult
     */
    public static function buildRedirectRequestResult(?string $redirectURL = null): RequestResult
    {
        return new RequestResult(RequestResultType::REDIRECT, null, null, $redirectURL);
    }

    /**
     * Helper function to build RequestResult instances for erroneous responses, providing the HTTP status code
     * @since 1.0
     * @param int $statusCode HTTP status code of the response
     * @param ?string $message Message
     * @return RequestResult
     */
    public static function buildStatusRequestResult(int $statusCode = 400, ?string $message = null): RequestResult
    {
        return new RequestResult(RequestResultType::STATUS, $message, null, null, $statusCode, true);
    }

    /**
     * Helper function to build RequestResult instances for JSON responses
     * @since 1.0
     * @param mixed $data JSON data
     * @param bool $preformatted If set, $data contains preformatted JSON data
     * @param int $statusCode HTTP status code of the response
     * @return RequestResult
     */
    public static function buildJSONRequestResult(mixed $data, bool $preformatted = false, int $statusCode = 200): RequestResult
    {
        return new RequestResult(RequestResultType::JSON, $data, null, null, $statusCode, $preformatted);
    }

    /**
     * Helper function to build RequestResult instances for XML responses
     * @since 1.0
     * @param mixed $data XML data
     * @param bool $preformatted If set, $data contains preformatted XML data
     * @param int $statusCode HTTP status code of the response
     * @return RequestResult
     */
    public static function buildXMLRequestResult(mixed $data, bool $preformatted = false, int $statusCode = 200): RequestResult
    {
        return new RequestResult(RequestResultType::XML, $data, null, null, $statusCode, $preformatted);
    }
}
