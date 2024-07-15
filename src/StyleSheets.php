<?php

/**
 * Style sheets management helper
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;
use chsxf\MFX\Services\IStyleSheetService;
use chsxf\MFX\Services\ITemplateService;

/**
 * Exceptions dispatched by the StyleSheets class
 * @since 1.0
 */
class StyleSheetException extends MFXException
{
}

/**
 * Helper class for managing style sheets
 * @since 1.0
 */
final class StyleSheets implements IStyleSheetService
{
    /**
     * @var array Style sheets container
     */
    private array $styleSheets = array();

    public function __construct(private readonly ITemplateService $templateService)
    {
    }

    /**
     * Adds a style sheets to the document
     * @since 1.0
     * @param string $url Style sheet URL or path for inline sheets
     * @param string $media Media type (Defaults to screen)
     * @param bool $inline If set, the style sheet is included inline in the response (Defaults to false).
     * @param bool $prepend If set, the style sheet is added before any other (Defaults to false).
     * @param string $type Style sheet type (Defaults to text/css).
     * @throws StyleSheetException If the URL is empty, or if the file does not exists or is not readable for inline sheets.
     */
    public function add(string $url, string $media = 'screen', bool $inline = false, bool $prepend = false, string $type = 'text/css')
    {
        if (empty($url)) {
            throw new StyleSheetException("'{$url} is not a valid style sheet URL.");
        }

        if (preg_match('#^mfx(css|js)://#', $url)) {
            $inline = true;
        }

        $url = $this->templateService->convertFakeProtocols($url);
        if (!empty($inline) && (!file_exists($url) || !is_file($url) || !is_readable($url))) {
            throw new StyleSheetException("'{$url} is not a valid style sheet URL.");
        }

        if (empty($inline) && !preg_match('#^(.+:)?//#', $url)) {
            $regs = null;
            if (preg_match('/^(.+)\.(\w+)$/', $url, $regs) && file_exists($url)) {
                $mtime = filemtime($url);
                $url = sprintf("%s_%d.%s", $regs[1], $mtime, $regs[2]);
            }
        }

        $obj = (object) array(
            'url' => $url,
            'media' => $media,
            'inline' => !empty($inline),
            'type' => $type,
            'content' => empty($inline) ? null : file_get_contents($url)
        );
        if ($prepend) {
            array_unshift($this->styleSheets, $obj);
        } else {
            $this->styleSheets[] = $obj;
        }
    }

    /**
     * Exports the HTML output for inclusion in the response `<head>` tag
     * @since 1.0
     * @return string
     */
    public function export(): string
    {
        return $this->templateService->getTwig()->render('@mfx/StyleSheets.twig', array('sheets' => $this->styleSheets));
    }
}
