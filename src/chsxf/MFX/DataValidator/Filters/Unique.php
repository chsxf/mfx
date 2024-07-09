<?php

/**
 * Data validation unique values filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating when a repeatable field contains only unique values.
 * @since 1.0
 */
class Unique extends AbstractFilter
{
    /**
     * Constructor
     * @since 1.0
     * @param string $message Error message
     */
    public function __construct(?string $message = null)
    {
        if (empty($message)) {
            $message = dgettext('mfx', "The field '%s' must contain unique values.");
        }
        parent::__construct($message);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see AbstractFilter::appliesToField()
     */
    public function appliesToField(): bool
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see AbstractFilter::validate()
     *
     * @param string $fieldName Field name
     * @param mixed $value Value to validate
     * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
     * @param boolean $silent If set, no error is triggered (defaults to false)
     *
     * Note:
     * The $atIndex parameter is ignored for filters returning true in appliesToField().
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        if (!is_array($value)) {
            return true;
        }

        $value = array_filter($value, function ($item) {
            return ($item !== null && $item !== '');
        });
        if (empty($value)) {
            return true;
        }
        $cv = array_count_values($value);
        if (max($cv) != 1) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
