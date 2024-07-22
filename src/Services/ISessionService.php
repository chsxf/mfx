<?php

namespace chsxf\MFX\Services;

use ArrayAccess;

interface ISessionService extends ArrayAccess
{
    function setInSession(array $values);
    function unsetInSession(string ...$keys);
}
