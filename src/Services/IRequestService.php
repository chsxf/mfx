<?php

namespace chsxf\MFX\Services;

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
     * Sets the attachment headers to use in response to the request
     * @param string $filename Filename
     * @param string $mimeType MIME type to assign to the response
     * @param string $charset Charset to use (defaults to UTF-8)
     * @param bool $addContentType Adds the Content-Type header if set (set by default)
     */
    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true): void;
}
