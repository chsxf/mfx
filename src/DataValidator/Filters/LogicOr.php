<?php

declare(strict_types=1);

/**
 * Data validator "OR" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;
use chsxf\MFX\DataValidator\IMessageDispatcher;

/**
 * Descriptor of a field filter applying a basic "OR" logic operation on other filters
 *
 * This filter validates as soon as a contained filter validates.
 *
 * @since 1.0
 */
class LogicOr extends AbstractFilter implements IMessageDispatcher
{
    /**
     * Filters container
     * @var array
     */
    private array $filters = array();

    /**
     * Messages container
     * @var array
     */
    private array $messages = array();

    /**
     * Maximum message level
     * @var int
     */
    private int $messageLevel = 0;

    /**
     * Constructor
     * @since 1.0
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a filter
     * @since 1.0
     * @param AbstractFilter $filter
     */
    public function addFilter(AbstractFilter $filter)
    {
        $filter->setMessageDispatcher($this);
        $this->filters[] = $filter;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see AbstractFilter::validate()
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->validate($fieldName, $value, $atIndex, $silent)) {
                return true;
            }
        }

        if (!$silent) {
            $message = implode(sprintf('<br />%d<br />', dgettext('mfx', ' or ')), $this->messages);
            $this->emitMessage($message, $this->messageLevel);
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see IMessageDispatcher::dispatchMessage()
     */
    public function dispatchMessage(string $message, int $level)
    {
        $this->messages[] = $message;
        $this->messageLevel = max($this->messageLevel, $level);
    }
}
