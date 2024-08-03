<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Services\IRequestService;

final class RequestServiceProxy implements IRequestService
{
    public function __construct(private readonly IRequestService $requestService)
    {
    }

    public function getRootURL(): string
    {
        return $this->requestService->getRootURL();
    }

    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true)
    {
        $this->requestService->setAttachmentHeaders($filename, $mimeType, $charset, $addContentType);
    }
}
