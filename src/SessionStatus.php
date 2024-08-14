<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Enumeration of the various session statuses - For internal use only
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @ignore
 * @since 2.0
 */
enum SessionStatus: string
{
    case active = 'active';
    case deleted = 'deleted';
    case migrated = 'migrated';
}
