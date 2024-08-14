<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Math helpers
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class MathTools
{
    /**
     * Generates a randomly-generated float number
     * comprised between 0.0 (inclusive) and 1.0 (inclusive)
     * @since 1.0
     * @return float
     */
    public static function randomFloat(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}
