<?php

namespace chsxf\MFX\Services;

use Twig\Environment;

interface ITemplateService
{
    public function getTwig(): ?Environment;
    public function convertFakeProtocols(string $str): string;
}
