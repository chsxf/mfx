<?php

/**
 * Data validation abstract field-matching filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Description of a filter validating when the field's matched another field's one
 * @since 1.0
 */
abstract class AbstractOtherFieldFilter extends AbstractFilter
{
    /**
     * @var array References to the matching fields
     */
    private array $otherFields;

    /**
     * Constructor
     * @since 1.0
     * @param Field|array $otherFields One or more references to the matching fields
     * @param string $message Error message
     */
    public function __construct(Field|array $otherFields, ?string $message = null)
    {
        parent::__construct($message);

        if ($otherFields instanceof Field === false) {
            $valid = true;
            if (is_array($otherFields)) {
                foreach ($otherFields as $v) {
                    if ($v instanceof Field === false) {
                        $valid = false;
                        break;
                    }
                }
            } else {
                $valid = false;
            }
            if (!$valid) {
                throw new DataValidatorException(dgettext('mfx', "References to other fields must be provided as a Field instance or an array containing only instances of this class."));
            }
        }

        $this->otherFields = is_array($otherFields) ? $otherFields : array($otherFields);
    }

    /**
     * Gets the references to the matching fields
     * @since 1.0
     * @return array
     */
    protected function getOtherFields(): array
    {
        return $this->otherFields;
    }
}
