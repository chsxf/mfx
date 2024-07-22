<?php

namespace chsxf\MFX;

enum SessionStatus: string
{
    case active = 'active';
    case deleted = 'deleted';
    case migrated = 'migrated';
}
