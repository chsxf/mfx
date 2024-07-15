<?php

namespace chsxf\MFX\Services;

use Twig\Environment;

interface ITemplateService
{
    function getTwig(): ?Environment;
    function convertFakeProtocols(string $str): string;
}
