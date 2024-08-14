<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Interface used to prevent objects implementing it from being filtered by JSONTools::filterAndEncode() and XMLTools::build()
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 * @see JSONTools::filterAndEncode()
 * @see XMLTools::build()
 */
interface IUnfilteredSerializable
{
}
