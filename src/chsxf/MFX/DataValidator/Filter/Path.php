<?php

/**
 * Data validator file path based field filter class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a field filter based on file path
 */
class Path extends AbstractFilter
{

	/**
	 * @var string Root path holder
	 */
	private string $_root;

	/**
	 * Constructor
	 * @param string $root Root path to look file into. Defaults to current working directory.
	 * @param string $message Error message
	 */
	public function __construct(string $root = './', ?string $message = NULL)
	{
		if ($message == null) {
			$message = dgettext('mfx', "The '%s' field does not contain an existing path.");
		}
		parent::__construct($message);

		$this->_root = $root;
	}

	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		$fp = $this->_root . $value;
		if (!file_exists($fp)) {
			if (!$silent) {
				$this->emitMessage($fieldName);
			}
			return false;
		}
		return true;
	}
}
