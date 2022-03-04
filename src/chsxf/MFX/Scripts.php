<?php

/**
 * Scripts management helper
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use Twig\Environment;

/**
 * Exceptions dispatched by the Scripts class
 */
class ScriptException extends \Exception
{
}

/**
 * Helper class for managing scripts
 */
class Scripts
{
	/**
	 * @var array Scripts container
	 */
	private static array $scripts = array();

	/**
	 * Adds a script to the document
	 * @param string $url Script URL or path for inline scripts
	 * @param string $inline If set, the script is included inline in the response (Defaults to false).
	 * @param string $prepend If set, the script is added before any other (Defaults to false).
	 * @param string $type Script type (Defaults to text/javascript).
	 * @throws ScriptException If the URL is empty, or if the file does not exists or is not readable for inline scripts.
	 */
	public static function add(string $url, bool $inline = false, bool $prepend = false, string $type = 'text/javascript')
	{
		if (empty($url)) {
			throw new ScriptException("'{$url} is not a valid script URL.");
		}

		$url = CoreManager::convertFakeProtocols($url);
		if (!empty($inline) && (!file_exists($url) || !is_file($url) || !is_readable($url))) {
			throw new ScriptException("'{$url} is not a valid script URL.");
		}

		if (empty($inline) && !preg_match('#^(.+:)?//#', $url) && strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) == 'js') {
			$regs = NULL;
			if (preg_match('/^(.+)\.(\w+)$/', $url, $regs) && file_exists($url)) {
				$mtime = filemtime($url);
				$url = sprintf("%s_%d.%s", $regs[1], $mtime, $regs[2]);
			}
		}

		$obj = (object) array(
			'url' => $url,
			'inline' => !empty($inline),
			'type' => $type,
			'content' => empty($inline) ? NULL : file_get_contents($url)
		);
		if ($prepend) {
			array_unshift(self::$scripts, $obj);
		} else {
			self::$scripts[] = $obj;
		}
	}

	/**
	 * Exports the HTML output for inclusion is the response <head> tag
	 * @param \Twig_Environment $twig Twig environnement used for rendering HTML
	 * @return string
	 */
	public static function export(Environment $twig): string
	{
		foreach (self::$scripts as &$v) {
			if ($v->inline) {
				$v->content = $twig->render($v->content);
			}
		}
		return $twig->render('@mfx/Scripts.twig', array('scripts' => self::$scripts));
	}
}
