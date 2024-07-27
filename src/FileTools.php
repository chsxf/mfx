<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\MFXException;

/**
 * File management helpers
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
class FileTools
{
    /**
     * Get file MIME type
     * @since 1.0
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
                throw new MFXException(HttpStatusCodes::unauthorized);
        }
    }
}
