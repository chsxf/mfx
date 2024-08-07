<?php

/**
 * Class and helper functions for math
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Math helper class
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
