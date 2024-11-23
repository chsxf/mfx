<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\RequestMethod;
use chsxf\MFX\Services\IRequestService;

/**
 * @since 2.0
 * @ignore
 */
final class RequestServiceProxy implements IRequestService
{
    public function __construct(private readonly IRequestService $requestService) {}

    public function getRootURL(): string
    {
        return $this->requestService->getRootURL();
    }

    public function getRequestMethod(): RequestMethod
    {
        return $this->requestService->getRequestMethod();
    }

    public function getRequestContentType(): ?string
    {
        return $this->requestService->getRequestContentType();
    }

    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true): void
    {
        $this->requestService->setAttachmentHeaders($filename, $mimeType, $charset, $addContentType);
    }
}
