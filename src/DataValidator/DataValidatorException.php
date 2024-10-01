<?php

declare(strict_types=1);

namespace chsxf\MFX\DataValidator;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\HttpStatusCodes;
use Throwable;

/**
 * Data validator exception class
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
class DataValidatorException extends MFXException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct(HttpStatusCodes::internalServerError, $message, $previous);
    }
}
