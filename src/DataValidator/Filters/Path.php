<?php

declare(strict_types=1);

/**
 * Data validator file path based field filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a field filter based on file path
 * @since 1.0
 */
class Path extends AbstractFilter
{
    /**
     * @var string Root path holder
     */
    private string $root;

    /**
     * Constructor
     * @since 1.0
     * @param string $root Root path to look file into. Defaults to current working directory.
     * @param string $message Error message
     */
    public function __construct(string $root = './', ?string $message = null)
    {
        if ($message == null) {
            $message = dgettext('mfx', "The '%s' field does not contain an existing path.");
        }
        parent::__construct($message);

        $this->root = $root;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see AbstractFilter::validate()
     *
     * @param string $fieldName Field's name
     * @param mixed $value Field's value
     * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
     * @param boolean $silent If set, no error is triggered (defaults to false)
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        $fp = $this->root . $value;
        if (!file_exists($fp)) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
