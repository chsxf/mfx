<?php
/**
 * Class and helper functions for file management
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

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
	public static function mimeTypeFromExtension($ext) {
		switch (strtolower($ext)) {
			case 'zip':
			case 'pdf':
				return "application/{$ext}";
				
			case 'jpg':
				return 'image/jpeg';
			
			case 'apk':
				return 'application/vnd.android.package-archive';
				
			default:
				CoreManager::dieWithStatusCode(401);
		}
	}
}