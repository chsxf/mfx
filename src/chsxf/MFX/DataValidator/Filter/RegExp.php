<?php

/**
 * Data validator regular expression based field filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filter;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field based on regular expressions
 */
class RegExp extends AbstractFilter
{
	const REGEX_WORD = '/^[a-z0-9_]+$/i';
	const REGEX_LCWORD = '/^[a-z0-9_]+$/';
	const REGEX_UCWORD = '/^[A-Z0-9_]+$/';
	const REGEX_LCALPHANUMERIC = '/^[a-z0-9]+$/';
	const REGEX_UCALPHANUMERIC = '/^[A-Z0-9]+$/';
	const REGEX_LCHEXADECIMAL = '/^[a-f0-9]+$/';
	const REGEX_UCHEXADECIMAL = '/^[A-F0-9]+$/';
	const REGEX_ICHEXADECIMAL = '/^[a-f0-9]+$/i';
	const REGEX_SQL_DATETIME = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

	/**
	 * @var string Regular expression holder
	 */
	private string $_regexp;

	/**
	 * Constructor
	 *
	 * @param string $regexp Perl-Compatible regular expression (PCRE)
	 * @param string $message Error message
	 * @see preg_match()
	 */
	public function __construct(string $regexp, ?string $message = NULL)
	{
		if ($message === null) {
			$message = dgettext('mfx', "The '%s' field does not match the specified regular expression.");
		}
		parent::__construct($message);

		$this->_regexp = $regexp;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractFilter::validate()
	 *
	 * @param string $fieldName Field's name
	 * @param mixed $value Field's value
	 * @param int $atIndex Index for repeatable fields. If -1, no index is provided. (Defaults to -1)
	 * @param boolean $silent If set, no error is triggered (defaults to false)
	 */
	public function validate(string $fieldName, mixed $value, int $atIndex = -1, bool $silent = false): bool
	{
		$res = preg_match($this->_regexp, $value);
		if (empty($res)) {
			if (!$silent) {
				$this->emitMessage($fieldName);
			}
			return false;
		}
		return true;
	}

	/**
	 * Helper function to build string length validation filters
	 *
	 * @param int $min Minimum required length. If empty or negative, no minimum is required. (Defaults to NULL)
	 * @param int $max Maximum required length. If empty or negative, no maximum is required. (Defaults to NULL)
	 * @return RegExp
	 */
	public static function stringLength(?int $min = NULL, ?int $max = NULL): RegExp
	{
		$min = is_scalar($min) ? intval($min) : 0;
		$max = is_scalar($max) ? intval($max) : 0;

		if (empty($min) && empty($max)) {
			$regexp = '/^.*$/';
			$message = NULL;
		} else {
			$regexp = '/^.{';
			if ($min > 0 && $min == $max) {
				$regexp .= $min;
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain exactly %d characters."), $min);
			} else if ($min > 0 && $max > 0) {
				$regexp .= "{$min},{$max}";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain between %d and %d characters (inclusive)."), $min, $max);
			} else if ($min > 0) {
				$regexp .= "{$min},";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at least %d characters."), $min);
			} else if ($max > 0) {
				$regexp .= "0,{$max}";
				$message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at most %d characters."), $max);
			}
			$regexp .= '}$/';
		}
		return new RegExp($regexp, $message);
	}

	/**
	 * Helper function to build case-insensitive word validation filter
	 *
	 * @return RegExp
	 */
	public static function word(): RegExp
	{
		return new RegExp(self::REGEX_WORD, dgettext('mfx', "The field '%s' does not contain a word."));
	}

	/**
	 * Helper function to build lower case validation filter
	 *
	 * @return RegExp
	 */
	public static function lowerCaseWord(): RegExp
	{
		return new RegExp(self::REGEX_LCWORD, dgettext('mfx', "The field '%s' does not contain a lower case word."));
	}

	/**
	 * Helper function to build upper case validation filter
	 *
	 * @return RegExp
	 */
	public static function upperCaseWord(): RegExp
	{
		return new RegExp(self::REGEX_UCWORD, dgettext('mfx', "The field '%s' does not contain an upper case word."));
	}

	/**
	 * Helper function to build variable bit-length hexadecimal key validation filter
	 *
	 * @param int $_bitLength Bit-length of the key (must be a multiple of 8)
	 * @param bool $_ignoreCase If set, produces a case-insensitive validation filter
	 * @param bool $_lowerCase If set and case is not ignored, the validation filter will constrain to lower case. If not set and case is not ignored, the validation filter will constrain to upper case.
	 * @return RegExp
	 */
	public static function hexKey(int $_bitLength, bool $_ignoreCase = false, bool $_lowerCase = true): RegExp
	{
		$bytes = floor($_bitLength / 8);
		$hexLength = $bytes * 2;

		$chars = $_lowerCase ? 'a-z' : 'A-Z';
		$regexp = "/^[{$chars}0-9]{{$hexLength}}$/";
		if ($_ignoreCase) {
			$regexp .= 'i';
			$message = dgettext('mfx', "The field '%%s' does not contain a %d bits hexadecimal key.");
		} else if ($_lowerCase) {
			$message = dgettext('mfx', "The field '%%s' does not contain a %d bits lower case hexadecimal key.");
		} else {
			$message = dgettext('mfx', "The field '%%s' does not contain a %d bits upper case hexadecimal key.");
		}

		$message = sprintf($message, $_bitLength);
		return new RegExp($regexp, $message);
	}

	/**
	 * Helper function to build a equality validation filter
	 *
	 * @param string $value Exact value to match
	 * @return RegExp
	 */
	public static function equals(string $value, bool $caseInsensitive = false): RegExp
	{
		$regexp = sprintf('/^%s$/%s', preg_quote($value, '/'), empty($caseInsensitive) ? '' : 'i');
		return new RegExp($regexp, sprintf(dgettext('mfx', "The field '%%s' does not equal the value '%s'."), $value));
	}
}
