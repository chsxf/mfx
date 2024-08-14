<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

use Twig\Environment;

/**
 * Template service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface ITemplateService
{
    /**
     * Retrieves the current Twig environment if existing
     * @return null|Environment
     */
    public function getTwig(): ?Environment;

    /**
     * Converts fake protocols in the provided string
     * @param string $str Input string
     * @return string The converted string with expanded fake protocols
     */
    public function convertFakeProtocols(string $str): string;
}
