<?php

namespace chsxf\MFX\Services;

use ArrayAccess;

interface ISessionService extends ArrayAccess
{
    public function setInSession(array $values);
    public function unsetInSession(string ...$keys);
}
