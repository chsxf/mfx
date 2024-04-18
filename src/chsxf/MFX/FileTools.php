<?php

/**
 * Class and helper functions for file management
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * File management helper class
 */
class FileTools
{
	/**
	 * Get file MIME type
	 * @param string $ext File extension
	 * @return string
	 */
	public static function mimeTypeFromExtension(string $ext): string
	{
		$lowercaseExt = strtolower($ext);
		switch ($lowercaseExt) {
			case 'zip':
			case 'pdf':
				return "application/{$lowercaseExt}";

			case 'jpg':
				return 'image/jpeg';

			case 'apk':
				return 'application/vnd.android.package-archive';

			default:
				CoreManager::dieWithStatusCode(401);
		}
	}
}
