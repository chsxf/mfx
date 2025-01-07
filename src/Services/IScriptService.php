<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

/**
 * Script service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IScriptService
{
    /**
     * Adds a script to the document
     * @param string $url Script URL or path for inline scripts
     * @param bool $inline If set, the script is included inline in the response (Defaults to false).
     * @param bool $prepend If set, the script is added before any other (Defaults to false).
     * @param string $type Script type (Defaults to text/javascript).
     * @param bool $defer If set and not inlined, the `defer` attribute will be added to the `<script>` tag.
     */
    public function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript', bool $defer = false): void;
}
