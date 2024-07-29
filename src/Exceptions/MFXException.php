<?php

namespace chsxf\MFX\Exceptions;

use chsxf\MFX\HttpStatusCodes;
use Exception;
use Throwable;

/**
 * Base exception class for all MFX code
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
class MFXException extends Exception
{
    /**
     * Constructor
     *
     * @since 2.0
     *
     * @param HttpStatusCodes $code HTTP status code associated to the Exception (defaults to 400 Bad Request)
     * @param string $message The exception message
     * @param null|Throwable $previous The previously thrown exception
     */
    public function __construct(HttpStatusCodes $code = HttpStatusCodes::badRequest, string $message = '', ?Throwable $previous = null)
    {
        if (empty($message)) {
            $message = $code->getStatusMessage();
        }
        parent::__construct($message, $code->value, $previous);
    }

    /**
     * Returns the HTTP status code associated with the exception
     *
     * @since 2.0
     *
     * @return HttpStatusCodes
     */
    public function getHttpCode(): HttpStatusCodes
    {
        return HttpStatusCodes::from($this->getCode());
    }
}
