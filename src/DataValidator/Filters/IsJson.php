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
class IsJson extends AbstractFilter
{
    /**
     * Constructor
     * @since 1.0
     * @param string $message Error message
     */
    public function __construct(?string $message = null)
    {
        if ($message === null) {
            $message = sprintf(dgettext('mfx', "The value of the '%%s' field must be valid JSON data"));
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
        $isOk = json_validate($value);

        if (!$isOk) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
