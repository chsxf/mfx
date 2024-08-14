<?php

declare(strict_types=1);

/**
 * Data validator "in range of integer values" field filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field checking presence of the value in a Min Max range
 * @since 1.0
 */
class InIntRange extends AbstractFilter
{
    private int $min;

    private int $max;

    private bool $includeMax;

    /**
     * Constructor
     * @since 1.0
     * @param int $min the minimum value
     * @param int $max the maximum value
     * @param bool $includeMax If set, includes the max value. If not, the max value is not part of the range.
     * @param string $message Error message
     */
    public function __construct(int $value1, int $value2, bool $includeMax = false, ?string $message = null)
    {
        $this->min = min($value1, $value2);
        $this->max = max($value1, $value2);

        $this->includeMax = $includeMax;

        if ($message === null) {
            $inclusivity = $this->includeMax ? dgettext('mfx', 'inclusive') : dgettext('mfx', 'exclusive');
            $message = sprintf(dgettext('mfx', "The value of the '%%s' field must fall in the range between %d and %d (%s)"), $this->min, $this->max, $inclusivity);
        }

        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     * @ignore
     * @see AbstractFilter::validate()
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        $minOk = ($value >= $this->min);
        $maxOk = $this->includeMax ? ($value <= $this->max) : ($value < $this->max);

        if (!$minOk || !$maxOk) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
