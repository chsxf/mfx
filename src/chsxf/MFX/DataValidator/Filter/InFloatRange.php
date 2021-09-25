<?php
/**
 * Data validator "in range of float values" field filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field checking presence of the value in a Min Max range
 */
class InFloatRange extends AbstractFilter {

	private $min;

	private $max;

	private $includeMax;

	/**
	 * Constructor
	 *
	 * @param float $min the minimum value
	 * @param float $max the maximum value
	 * @param bool $includeMax If set, includes the max value. If not, the max value is not part of the range.
	 * @param string $message Error message
	 */
	public function __construct(float $_value1, float $_value2, bool $_includeMax = false, $message = NULL) {
		$this->min = min($_value1, $_value2);
		$this->max = max($_value1, $_value2);

		$this->includeMax = $_includeMax;

		if ($message === NULL) {
			$inclusivity = $this->includeMax ? dgettext('mfx', 'inclusive') : dgettext('mfx', 'exclusive');
			$message = sprintf(dgettext('mfx', "The value of the '%%s' field must fall in the range between %f and %f (%s)"), $this->min, $this->max, $inclusivity);
		}

		parent::__construct($message);
	}

	/**
	 * {@inheritdoc}
	 * @see AbstractFilter::validate()
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		$minOk = ($value >= $this->min);
		$maxOk = $this->includeMax ? ($value <= $this->max) : ($value < $this->max);

		if (!$minOk || !$maxOk) {
			if (empty($silent)) {
				$this->emitMessage($fieldName);
			}

			return false;
		}
		else
			return true;
	}

}