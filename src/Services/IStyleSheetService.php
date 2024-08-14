<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

/**
 * StyleSheet service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IStyleSheetService
{
    /**
     * Adds a style sheets to the document
     * @param string $url Style sheet URL or path for inline sheets
     * @param string $media Media type (Defaults to screen)
     * @param bool $inline If set, the style sheet is included inline in the response (Defaults to false).
     * @param bool $prepend If set, the style sheet is added before any other (Defaults to false).
     * @param string $type Style sheet type (Defaults to text/css).
     */
    public function add(string $url, string $media = 'screen', bool $inline = false, bool $prepend = false, string $type = 'text/css'): void;
}
