<?php
/**
 * Data validator regular expression based field filter class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX\DataValidator\Filter;

use CheeseBurgames\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field based on regular expressions
 */
class RegExp extends AbstractFilter
{
	public const REGEX_WORD = '/^[a-z0-9_]+$/i';
	public const REGEX_LCWORD = '/^[a-z0-9_]+$/';
	public const REGEX_UCWORD = '/^[A-Z0-9_]+$/';
	public const REGEX_LCALPHANUMERIC = '/^[a-z0-9]+$/';
	public const REGEX_UCALPHANUMERIC = '/^[A-Z0-9]+$/';
	
	/**
	 * @var string Regular expression holder
	 */
	private $_regexp;
	
	/**
	 * Constructor
	 * @param string $regexp Perl-Compatible regular expression (PCRE)
	 * @param string $message Error message
	 * 
	 * @see preg_match()
	 */
	public function __construct($regexp, $message = NULL) {
		if ($message === NULL)
			$message = dgettext('mfx', "The '%s' field does not match the specified regular expression.");
		parent::__construct($message);
		
		$this->_regexp = $regexp;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractFilter::validate()
	 * 
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If NULL, no index is provided. (Defaults to NULL)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate($fieldName, $value, $atIndex = NULL, $silent = false) {
		$res = preg_match($this->_regexp, $value);
		if (empty($res))
		{
			if (empty($silent))
				$this->emitMessage($fieldName);
			return false;
		}
		else
			return true;
	}
	
	/**
	 * Helper function to build string length validation filters
	 * @param int $min Minimum required length. If empty or negative, no minimum is required. (Defaults to NULL)
	 * @param int $max Maximum required length. If empty or negative, no maximum is required. (Defaults to NULL) 
	 * @return RegExp
	 */
	public static function stringLength($min = NULL, $max = NULL) {
		$min = is_scalar($min) ? intval($min) : 0;
		$max = is_scalar($max) ? intval($max) : 0;
		
		if (empty($min) && empty($max))
		{
			$regexp = '/^.*$/';
			$message = NULL;
		}
		else
		{
			$regexp = '/^.{';
			if ($min > 0 && $min == $max)
			{
				$regexp .= $min;
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain exactly %d characters."), $min);
			}
			else if ($min > 0 && $max > 0)
			{
				$regexp .= "{$min},{$max}";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain between %d and %d characters (inclusive)."), $min, $max);
			}
			else if ($min > 0)
			{
				$regexp .= "{$min},";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at least %d characters."), $min);
			}
			else if ($max > 0)
			{
				$regexp .= "0,{$max}";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at most %d characters."), $max);
			}
			$regexp .= '}$/';
		}
		return new RegExp($regexp, $message);
	}
	
	/**
	 * Helper function to build case-insensitive word validation filter
	 * @return RegExp
	 */
	public static function word() {
		return new RegExp(self::REGEX_WORD, dgettext('mfx', "The field '%s' does not contain a word."));
	}
	
	/**
	 * Helper function to build lower case validation filter
	 * @return RegExp
	 */
	public static function lowerCaseWord() {
		return new RegExp(self::REGEX_LCWORD, dgettext('mfx', "The field '%s' does not contain a lower case word."));
	}
	
	/**
	 * Helper function to build upper case validation filter
	 * @return RegExp
	 */
	public static function upperCaseWord() {
		return new RegExp(self::REGEX_UCWORD, dgettext('mfx', "The field '%s' does not contain an upper case word."));
	}
	
	/**
	 * Helper function to build a equality validation filter
	 * @param string $value Exact value to match
	 * @return RegExp
	 */
	public static function equals($value, $caseInsensitive = false) {
		$regexp = sprintf('/^%s$/%s', preg_quote($value, '/'), empty($caseInsensitive) ? '' : 'i');
		return new RegExp($regexp, sprintf(dgettext('mfx', "The field '%%s' does not equal the value '%s'."), $value));
	}
}