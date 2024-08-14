<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Enumeration of HTTP status codes
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
enum HttpStatusCodes: int
{
    case continue = 100;
    case switchingProtocols = 101;
    case ok = 200;
    case created = 201;
    case accepted = 202;
    case nonAuthoritativeInformation = 203;
    case noContent = 204;
    case resetContent = 205;
    case partialContent = 206;
    case multipleChoices = 300;
    case movedPermanently = 301;
    case movedTemporarily = 302;
    case seeOther = 303;
    case notModified = 304;
    case useProxy = 305;
    case temporaryRedirect = 307;
    case permanentRedirect = 308;
    case tooManyRedirects = 310;
    case badRequest = 400;
    case unauthorized = 401;
    case paymentRequired = 402;
    case forbidden = 403;
    case notFound = 404;
    case methodNotAllowed = 405;
    case notAcceptable = 406;
    case proxyAuthenticationRequired = 407;
    case requestTimeOut = 408;
    case conflict = 409;
    case gone = 410;
    case lengthRequired = 411;
    case preconditionFailed = 412;
    case requestEntityTooLarge = 413;
    case requestUriTooLarge = 414;
    case unsupportedMediaType = 415;
    case requestedRangeUnsatisfiable = 416;
    case expectationFailed = 417;
    case upgradeRequired = 426;
    case preconditionRequired = 428;
    case tooManyRequests = 429;
    case requestHeaderFieldsTooLarge = 431;
    case retryWith = 449;
    case internalServerError = 500;
    case notImplemented = 501;
    case badGateway = 502;
    case serviceUnavailable = 503;
    case gatewayTimeOut = 504;
    case httpVersionNotSupported = 505;
    case bandwidthLimitExceeded = 509;
    case notExtended = 510;
    case networkAuthenticationRequired = 511;
    case webServerIsReturningAnUnknownError = 520;

    /**
     * Returns sur the status message associated with the code
     * @return string
     */
    public function getStatusMessage(): string
    {
        static $httpStatusCodes = array(
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
            416 => 'Requested Range Unsatisfiable',
            417 => 'Expectation Failed',
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
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            520 => 'Web Server is Returning an Unknown Error'
        );

        return $httpStatusCodes[$this->value];
    }
}
