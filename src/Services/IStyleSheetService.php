<?php

namespace chsxf\MFX\Services;

interface IStyleSheetService
{
    function add(string $url, string $media = 'screen', bool $inline = false, bool $prepend = false, string $type = 'text/css');
}
