<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

use chsxf\MFX\RequestMethod;

/**
 * Request service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IRequestService
{
    /**
     * Get the root URL for the request
     * @return string
     */
    public function getRootURL(): string;

    /**
     * Get the method used by the request
     * (ex: GET, POST...)
     * @return RequestMethod
     */
    public function getRequestMethod(): RequestMethod;

    /**
     * Get the content-type used by the request
     * (ex: application/json)
     * @since 2.0.1
     * @return null|string
     */
    public function getRequestContentType(): ?string;

    /**
     * Sets the attachment headers to use in response to the request
     * @param string $filename Filename
     * @param string $mimeType MIME type to assign to the response
     * @param string $charset Charset to use (defaults to UTF-8)
     * @param bool $addContentType Adds the Content-Type header if set (set by default)
     */
    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true): void;
}
