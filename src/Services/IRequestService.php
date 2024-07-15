<?php

namespace chsxf\MFX\Services;

interface IRequestService
{
    function getRootURL(): string;
    function setAttachmentHeaders(string $filename, string $mimeType, string $charset = 'UTF-8', bool $addContentType = true);
}
