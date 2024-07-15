<?php

namespace chsxf\MFX\Exceptions;

use chsxf\MFX\HttpStatusCodes;
use Throwable;

final class ConfigException extends MFXException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct(HttpStatusCodes::internalServerError, $message, $previous);
    }
}
