<?php
/**
 * Style sheets management helper
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

/**
 * Exceptions dispatched by the StyleSheets class
 */
class StyleSheetException extends \Exception { }

/**
 * Helper class for managing style sheets
 */
class StyleSheets
{
	/**
	 * @var array Style sheets container
	 */
	private static $styleSheets = array();
	
	/**
	 * Adds a style sheets to the document
	 * @param string $url Style sheet URL or path for inline sheets
	 * @param string $media Media type (Defaults to screen)
	 * @param string $inline If set, the style sheet is included inline in the response (Defaults to false).
	 * @param string $prepend If set, the style sheet is added before any other (Defaults to false).
	 * @param string $type Style sheet type (Defaults to text/css).
	 * @throws StyleSheetException If the URL is empty, or if the file does not exists or is not readable for inline sheets.
	 */
	public static function add($url, $media = 'screen', $inline = false, $prepend = false, $type = 'text/css')
	{
		if (empty($url) || (!empty($inline) && (!file_exists($url) || !is_file($url) || !is_readable($url))))
			throw new StyleSheetException("'{$url} is not a valid style sheet URL.");

		$url = CoreManager::convertFakeProtocols($url);
		if (empty($inline) && !Config::isGoogleAppEngineRuntime() && !preg_match('#^(.+:)?//#', $url))
		{
			$regs = NULL;
			if (preg_match('/^(.+)\.(\w+)$/', $url, $regs) && file_exists($url))
			{
				$mtime = filemtime($url);
				$url = sprintf("%s_%d.%s", $regs[1], $mtime, $regs[2]);
			}
		}
		
		$obj = (object) array(
				'url' => $url,
				'media' => $media,
				'inline' => !empty($inline),
				'type' => $type,
				'content' => empty($inline) ? NULL : file_get_contents($url)
		);
		if ($prepend)
			array_unshif(self::$styleSheets, $obj);
		else
			self::$styleSheets[] = $obj;
	}
	
	/**
	 * Exports the HTML output for inclusion is the response <head> tag
	 * @param Twig_Environment $twig Twig environnement used for rendering HTML
	 * @return string
	 */
	public static function export(\Twig_Environment $twig) {
		return $twig->render('@mfx/StyleSheets.twig', array( 'sheets' => self::$styleSheets ));
	}
}