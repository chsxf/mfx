<?php

/**
 * Data validation "value is of type" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;
use chsxf\MFX\DataValidator\DataValidatorException;
use chsxf\MFX\HttpStatusCodes;

/**
 * Description of a filter validating if the specified value is of a specific type
 * @since 1.0
 */
class IsOfType extends AbstractFilter
{
    /**
     * @var string Variable type
     */
    private string $type;

    /**
     * Constructor
     * @since 1.0
     * @param string $type Variable type
     * @param string $message Error message (Defaults to NULL)
     * @throws DataValidatorException
     */
    public function __construct(string $type, ?string $message = null)
    {
        if (!in_array($type, array('boolean', 'integer', 'double', 'string', 'array', 'object', 'resource', 'NULL'))) {
            throw new DataValidatorException(HttpStatusCodes::internalServerError, "type argument must be one of 'boolean', 'integer', 'double', 'string', 'array', 'object', 'resource' or 'NULL'");
        }
        $this->type = $type;

        if (empty($message)) {
            $message = sprintf(dgettext('mfx', "The '%%s' field value must be of type '%s'."), $type);
        }
        parent::__construct($message);
    }

    /**
     * {@inheritDoc}
     * @ignore
     * @see \chsxf\MFX\DataValidator\AbstractFilter::validate()
     */
    public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
    {
        if (gettype($value) != $this->type) {
            if (!$silent) {
                $this->emitMessage($fieldName);
            }
            return false;
        }
        return true;
    }
}
