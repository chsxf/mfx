<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\ScriptException;
use chsxf\MFX\Services\IScriptService;
use chsxf\MFX\Services\ITemplateService;

/**
 * Helper class for managing scripts
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class Scripts implements IScriptService
{
    /**
     * @var array Scripts container
     */
    private array $scripts = array();

    public function __construct(private readonly ITemplateService $templateService)
    {
    }

    /**
     * Adds a script to the document
     * @param string $url Script URL or path for inline scripts
     * @param string $inline If set, the script is included inline in the response (Defaults to false).
     * @param string $prepend If set, the script is added before any other (Defaults to false).
     * @param string $type Script type (Defaults to text/javascript).
     * @throws ScriptException If the URL is empty, or if the file does not exists or is not readable for inline scripts.
     */
    public function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript')
    {
        if (empty($url)) {
            throw new ScriptException(HttpStatusCodes::internalServerError, "'{$url} is not a valid script URL.");
        }

        if (preg_match('#^mfx(css|js)://#', $url)) {
            $inline = true;
        }

        $url = $this->templateService->convertFakeProtocols($url);
        if (!empty($inline) && (!file_exists($url) || !is_file($url) || !is_readable($url))) {
            throw new ScriptException(HttpStatusCodes::internalServerError, "'{$url} is not a valid script URL.");
        }

        if (empty($inline) && !preg_match('#^(.+:)?//#', $url) && strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) == 'js') {
            $regs = null;
            if (preg_match('/^(.+)\.(\w+)$/', $url, $regs) && file_exists($url)) {
                $mtime = filemtime($url);
                $url = sprintf("%s_%d.%s", $regs[1], $mtime, $regs[2]);
            }
        }

        $obj = (object) array(
            'url' => $url,
            'inline' => !empty($inline),
            'type' => $type,
            'content' => empty($inline) ? null : file_get_contents($url)
        );
        if ($prepend) {
            array_unshift($this->scripts, $obj);
        } else {
            $this->scripts[] = $obj;
        }
    }

    /**
     * Exports the HTML output for inclusion in the response `<head>` tag
     * @return string
     */
    public function export(): string
    {
        $twig = $this->templateService->getTwig();
        foreach ($this->scripts as &$v) {
            if ($v->inline) {
                $v->content = $twig->createTemplate($v->content)->render();
            }
        }
        return $twig->render('@mfx/Scripts.twig', array('scripts' => $this->scripts));
    }
}
