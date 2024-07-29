<?php

namespace chsxf\MFX\Services;

interface IRequestService
{
    public function getRootURL(): string;
    public function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true);
}
