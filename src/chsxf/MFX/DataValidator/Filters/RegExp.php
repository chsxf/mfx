<?php

/**
 * Data validator regular expression based field filter class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Filters;

use chsxf\MFX\DataValidator\AbstractFilter;

/**
 * Descriptor of a filter field based on regular expressions
 * @since 1.0
 */
class RegExp extends AbstractFilter
{
    public const REGEX_WORD = '/^[a-z0-9_]+$/i';
    public const REGEX_LCWORD = '/^[a-z0-9_]+$/';
    public const REGEX_UCWORD = '/^[A-Z0-9_]+$/';
    public const REGEX_LCALPHANUMERIC = '/^[a-z0-9]+$/';
    public const REGEX_UCALPHANUMERIC = '/^[A-Z0-9]+$/';
    public const REGEX_LCHEXADECIMAL = '/^[a-f0-9]+$/';
    public const REGEX_UCHEXADECIMAL = '/^[A-F0-9]+$/';
    public const REGEX_ICHEXADECIMAL = '/^[a-f0-9]+$/i';
    public const REGEX_SQL_DATETIME = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    /**
     * @var string Regular expression holder
     */
    private string $regexp;

    /**
     * Constructor
     * @since 1.0
     * @param string $regexp Perl-Compatible regular expression (PCRE)
     * @param string $message Error message
     * @see preg_match()
     */
    public function __construct(string $regexp, ?string $message = null)
    {
        if ($message === null) {
            $message = dgettext('mfx', "The '%s' field does not match the specified regular expression.");
        }
        parent::__construct($message);

        $this->regexp = $regexp;
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
        $res = preg_match($this->regexp, $value);
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
     * @since 1.0
     * @param int $min Minimum required length. If empty or negative, no minimum is required. (Defaults to NULL)
     * @param int $max Maximum required length. If empty or negative, no maximum is required. (Defaults to NULL)
     * @return RegExp
     */
    public static function stringLength(?int $min = null, ?int $max = null): RegExp
    {
        $min = is_scalar($min) ? intval($min) : 0;
        $max = is_scalar($max) ? intval($max) : 0;

        if (empty($min) && empty($max)) {
            $regexp = '/^.*$/';
            $message = null;
        } else {
            $regexp = '/^.{';
            if ($min > 0 && $min == $max) {
                $regexp .= $min;
                $message = sprintf(dgettext('mfx', "The string in field '%%s' must contain exactly %d characters."), $min);
            } elseif ($min > 0 && $max > 0) {
                $regexp .= "{$min},{$max}";
                $message = sprintf(dgettext('mfx', "The string in field '%%s' must contain between %d and %d characters (inclusive)."), $min, $max);
            } elseif ($min > 0) {
                $regexp .= "{$min},";
                $message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at least %d characters."), $min);
            } elseif ($max > 0) {
                $regexp .= "0,{$max}";
                $message = sprintf(dgettext('mfx', "The string in field '%%s' must contain at most %d characters."), $max);
            }
            $regexp .= '}$/';
        }
        return new RegExp($regexp, $message);
    }

    /**
     * Helper function to build case-insensitive word validation filter
     * @since 1.0
     * @return RegExp
     */
    public static function word(): RegExp
    {
        return new RegExp(self::REGEX_WORD, dgettext('mfx', "The field '%s' does not contain a word."));
    }

    /**
     * Helper function to build lower case validation filter
     * @since 1.0
     * @return RegExp
     */
    public static function lowerCaseWord(): RegExp
    {
        return new RegExp(self::REGEX_LCWORD, dgettext('mfx', "The field '%s' does not contain a lower case word."));
    }

    /**
     * Helper function to build upper case validation filter
     * @since 1.0
     * @return RegExp
     */
    public static function upperCaseWord(): RegExp
    {
        return new RegExp(self::REGEX_UCWORD, dgettext('mfx', "The field '%s' does not contain an upper case word."));
    }

    /**
     * Helper function to build variable bit-length hexadecimal key validation filter
     * @since 1.0
     * @param int $bitLength Bit-length of the key (must be a multiple of 8)
     * @param bool $ignoreCase If set, produces a case-insensitive validation filter
     * @param bool $lowerCase If set and case is not ignored, the validation filter will constrain to lower case. If not set and case is not ignored, the validation filter will constrain to upper case.
     * @return RegExp
     */
    public static function hexKey(int $bitLength, bool $ignoreCase = false, bool $lowerCase = true): RegExp
    {
        $bytes = floor($bitLength / 8);
        $hexLength = $bytes * 2;

        $chars = $lowerCase ? 'a-z' : 'A-Z';
        $regexp = "/^[{$chars}0-9]{{$hexLength}}$/";
        if ($ignoreCase) {
            $regexp .= 'i';
            $message = dgettext('mfx', "The field '%%s' does not contain a %d bits hexadecimal key.");
        } elseif ($lowerCase) {
            $message = dgettext('mfx', "The field '%%s' does not contain a %d bits lower case hexadecimal key.");
        } else {
            $message = dgettext('mfx', "The field '%%s' does not contain a %d bits upper case hexadecimal key.");
        }

        $message = sprintf($message, $bitLength);
        return new RegExp($regexp, $message);
    }

    /**
     * Helper function to build a equality validation filter
     * @since 1.0
     * @param string $value Exact value to match
     * @return RegExp
     */
    public static function equals(string $value, bool $caseInsensitive = false): RegExp
    {
        $regexp = sprintf('/^%s$/%s', preg_quote($value, '/'), empty($caseInsensitive) ? '' : 'i');
        return new RegExp($regexp, sprintf(dgettext('mfx', "The field '%%s' does not equal the value '%s'."), $value));
    }
}
