<?php

namespace chsxf\MFX\Services;

interface IScriptService
{
    function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript');
}
