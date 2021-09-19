<?php
/**
 * Data validation "value is of type" filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Description of a filter validating if the specified value is of a specific type
 */
class IsOfType extends AbstractFilter {
	
	/**
	 * @var string Variable type
	 */
	private $_type;
	
	/**
	 * Constructor
	 * @param string $type Variable type
	 * @param string $message Error message (Defaults to NULL)
	 * @throws \InvalidArgumentException
	 */
	public function __construct($type, string $message = NULL) {
		if (!in_array($type, array( 'boolean', 'integer', 'double', 'string', 'array', 'object', 'resource', 'NULL' )))
			throw new \InvalidArgumentException("type argument must be one of 'boolean', 'integer', 'double', 'string', 'array', 'object', 'resource' or 'NULL'");
		$this->_type = $type;
			
		if (empty($message))
			$message = sprintf(dgettext('mfx', "The '%%s' field value must be of type '%s'."), $type);
		parent::__construct ( $message = NULL );
	}
	
	/**
	 * {@inheritDoc}
	 * @see \chsxf\MFX\DataValidator\AbstractFilter::validate()
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		if (gettype($value) != $this->_type) {
			if (empty($silent))
				$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
}