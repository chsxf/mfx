<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * String management helpers
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
class StringTools
{
    public const CHARSET_ALPHA_LC = 'abcdefghijklmnopqrstuvwxyz';
    public const CHARSET_ALPHA_UC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHARSET_ALPHA_CI = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHARSET_NUMERIC = '0123456789';
    public const CHARSET_ALPHANUMERIC_LC = 'abcdefghijklmnopqrstuvwxyz0123456789';
    public const CHARSET_ALPHANUMERIC_UC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    public const CHARSET_ALPHANUMERIC_CI = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Checks if the specified address is a valid email address
     * @param string $address The email address string to check
     * @return boolean true if the address is valid, false either
     */
    public static function isValidEmailAddress(string $address): bool
    {
        return (bool) preg_match("/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~“”-]+|“.+”)(?:\.(?:[a-z0-9!#$%&'*+\/=?^_`{|}~“”-]+|“.+”))*@(?:\[?(?:[1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}\]?|(?:[a-z](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z](?:[a-z0-9-]*[a-z0-9])+)$/", $address);
    }

    /**
     * Checks if the specified string contains a positive integer, optionnaly equals to zero
     * @param string $str String to evaluate
     * @param boolean $canBeZero If set, the string can be evaluated to zero. If not, the integer must be positive. (Defaults to false)
     * @return boolean
     */
    public static function isPositiveInteger(string $str, bool $canBeZero = false): bool
    {
        if ($canBeZero) {
            $res = preg_match('/^(0|[1-9]\d*)$/', $str);
        } else {
            $res = preg_match('/^[1-9]\d*$/', $str);
        }
        return !empty($res);
    }

    /**
     * Checks if the specified string contains a negative integer, optionnaly equals to zero
     * @param string $str String to evaluate
     * @param boolean $canBeZero If set, the string can be evaluated to zero. If not, the integer must be negative. (Defaults to false)
     * @return boolean
     */
    public static function isNegativeInteger(string $str, bool $canBeZero = false): bool
    {
        if ($canBeZero) {
            $res = preg_match('/^(\-?0|\-[1-9]\d*)$/', $str);
        } else {
            $res = preg_match('/^\-[1-9]\d*$/', $str);
        }
        return !empty($res);
    }

    /**
     * Checks if the specified string contains an integer
     * @param string $str String to evaluate
     * @return boolean
     */
    public static function isInteger(string $str): bool
    {
        $res = preg_match('/^\-?(0|[1-9]\d*)$/', $str);
        return !empty($res);
    }

    /**
     * Generates a random string based on the specified character set
     * @param int $length Length of the resulting string
     * @param string $charset Character set (Defaults to alphanumeric lower case characters)
     * @return boolean|string Returns false if length is zero or negative or charset is not a non-empty string. Returns the generated string either.
     */
    public static function generateRandomString(int $length, string $charset = self::CHARSET_ALPHANUMERIC_LC): string|false
    {
        $length = intval($length);
        if (empty($charset) || !is_string($charset) || $length <= 0) {
            return false;
        }

        $charset_max_index = strlen($charset) - 1;
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $charset[mt_rand(0, $charset_max_index)];
        }
        return $str;
    }

    /**
     * Joins array elements with a separator string, eventually replacing the first and last separators with specific ones.
     * @param string $separator Separator string
     * @param array $elements Elements to join. If $elements is empty, the function returns an empty string. If $elements contains only one element, function returns this element.
     * @param string $lastSeparator Separator to use between the two last elements. If NULL, the general separator is used. (Defaults to NULL)
     * @param string $firstSeparator Separator to use between the two first elements. If NULL, the general separator is used. (Defaults to NULL)
     * @return string Note :
     *         If $elements contains only two elements and that both $lastSeperator and $firstSeparator are defined, $lastSeparator is used.
     */
    public static function implode(string $separator, array $elements, ?string $lastSeparator = null, ?string $firstSeparator = null): string
    {
        if (count($elements) <= 2) {
            return implode(($lastSeparator === null) ? $separator : $lastSeparator, $elements);
        } else {
            $elements = array_values($elements);
            $str = $elements[0];
            for ($i = 1; $i < count($elements); $i++) {
                if ($firstSeparator !== null && $i == 1) {
                    $str .= $firstSeparator;
                } elseif ($lastSeparator !== null && $i == count($elements) - 1) {
                    $str .= $lastSeparator;
                } else {
                    $str .= $separator;
                }
                $str .= $elements[$i];
            }
            return $str;
        }
    }

    /**
     * Converts a string in snake case to the same string in camel case
     * @param string $_str Input string
     * @return string Resulting string in camel case
     */
    public static function snakeToCamelCase(string $_str): string
    {
        return lcfirst(self::snakeToPascalCase($_str));
    }

    /**
     * Converts a string in snake case to the same string in Pascal case
     * @param string $_str Input string
     * @return string Resulting string in Pascal case
     */
    public static function snakeToPascalCase(string $_str): string
    {
        return str_replace('_', '', ucwords($_str, '_'));
    }

    /**
     * Converts a string in snake case
     * @param string $_str Input string
     * @param bool $_upperCase If set, returns the string in upper snake case.
     * @return string Resulting string in snake case
     */
    public static function toSnakeCase(string $_str, bool $_upperCase = false): string
    {
        $patterns = [
            '/([a-z\d])([A-Z])/',
            '/([^_])([A-Z][a-z])/',
            '/\s([a-z])/i'
        ];
        $replacements = ['$1_$2', '_$2', '_$1'];

        $snake = preg_replace($patterns, $replacements, $_str);
        return $_upperCase ? strtoupper($snake) : strtolower($snake);
    }

    /**
     * Sanitizes a string by removing accents, then transforming to lower case,
     * then replacing non-alphabetic and non-numeric characters by a place holder,
     * then removing consecutive multiple place holders.
     * Note:
     * Result string may start or end with the place holder.
     * @param string $string Source string
     * @param string $placeholder Placeholder text (Defaults to '-')
     * @return string
     */
    public static function sanitize(string $string, string $placeholder = '-', ?string $locale = null): string
    {
        $string = self::removeAccents($string, $locale);
        $string = strtolower($string);

        $string = preg_replace('/[^a-z0-9]/', $placeholder, $string);
        $regex = preg_quote($placeholder, '/');
        $string = preg_replace("/({$regex}){2,}/", $placeholder, $string);

        return $string;
    }

    /**
     * Removes accents from accented characters
     * @param string $string Source string
     * @return string
     */
    public static function removeAccents(string $string, ?string $locale = null): string
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(194) . chr(170) => 'a',
            chr(194) . chr(186) => 'o',
            chr(195) . chr(128) => 'A',
            chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A',
            chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A',
            chr(195) . chr(133) => 'A',
            chr(195) . chr(134) => 'AE',
            chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I',
            chr(195) . chr(144) => 'D',
            chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O',
            chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O',
            chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O',
            chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U',
            chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U',
            chr(195) . chr(157) => 'Y',
            chr(195) . chr(158) => 'TH',
            chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a',
            chr(195) . chr(166) => 'ae',
            chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e',
            chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e',
            chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i',
            chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i',
            chr(195) . chr(175) => 'i',
            chr(195) . chr(176) => 'd',
            chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(184) => 'o',
            chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y',
            chr(195) . chr(190) => 'th',
            chr(195) . chr(191) => 'y',
            chr(195) . chr(152) => 'O',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A',
            chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A',
            chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A',
            chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C',
            chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C',
            chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C',
            chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C',
            chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D',
            chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D',
            chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E',
            chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E',
            chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E',
            chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E',
            chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E',
            chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G',
            chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G',
            chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G',
            chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G',
            chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H',
            chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H',
            chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I',
            chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I',
            chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I',
            chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I',
            chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I',
            chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ',
            chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J',
            chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K',
            chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k',
            chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l',
            chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l',
            chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l',
            chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l',
            chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l',
            chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n',
            chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n',
            chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n',
            chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n',
            chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O',
            chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O',
            chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O',
            chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE',
            chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R',
            chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R',
            chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R',
            chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S',
            chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S',
            chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S',
            chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S',
            chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T',
            chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T',
            chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T',
            chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U',
            chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U',
            chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U',
            chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U',
            chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U',
            chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U',
            chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W',
            chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y',
            chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y',
            chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z',
            chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z',
            chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z',
            chr(197) . chr(191) => 's',
            // Decompositions for Latin Extended-B
            chr(200) . chr(152) => 'S',
            chr(200) . chr(153) => 's',
            chr(200) . chr(154) => 'T',
            chr(200) . chr(155) => 't',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            chr(198) . chr(160) => 'O',
            chr(198) . chr(161) => 'o',
            chr(198) . chr(175) => 'U',
            chr(198) . chr(176) => 'u',
            // grave accent
            chr(225) . chr(186) . chr(166) => 'A',
            chr(225) . chr(186) . chr(167) => 'a',
            chr(225) . chr(186) . chr(176) => 'A',
            chr(225) . chr(186) . chr(177) => 'a',
            chr(225) . chr(187) . chr(128) => 'E',
            chr(225) . chr(187) . chr(129) => 'e',
            chr(225) . chr(187) . chr(146) => 'O',
            chr(225) . chr(187) . chr(147) => 'o',
            chr(225) . chr(187) . chr(156) => 'O',
            chr(225) . chr(187) . chr(157) => 'o',
            chr(225) . chr(187) . chr(170) => 'U',
            chr(225) . chr(187) . chr(171) => 'u',
            chr(225) . chr(187) . chr(178) => 'Y',
            chr(225) . chr(187) . chr(179) => 'y',
            // hook
            chr(225) . chr(186) . chr(162) => 'A',
            chr(225) . chr(186) . chr(163) => 'a',
            chr(225) . chr(186) . chr(168) => 'A',
            chr(225) . chr(186) . chr(169) => 'a',
            chr(225) . chr(186) . chr(178) => 'A',
            chr(225) . chr(186) . chr(179) => 'a',
            chr(225) . chr(186) . chr(186) => 'E',
            chr(225) . chr(186) . chr(187) => 'e',
            chr(225) . chr(187) . chr(130) => 'E',
            chr(225) . chr(187) . chr(131) => 'e',
            chr(225) . chr(187) . chr(136) => 'I',
            chr(225) . chr(187) . chr(137) => 'i',
            chr(225) . chr(187) . chr(142) => 'O',
            chr(225) . chr(187) . chr(143) => 'o',
            chr(225) . chr(187) . chr(148) => 'O',
            chr(225) . chr(187) . chr(149) => 'o',
            chr(225) . chr(187) . chr(158) => 'O',
            chr(225) . chr(187) . chr(159) => 'o',
            chr(225) . chr(187) . chr(166) => 'U',
            chr(225) . chr(187) . chr(167) => 'u',
            chr(225) . chr(187) . chr(172) => 'U',
            chr(225) . chr(187) . chr(173) => 'u',
            chr(225) . chr(187) . chr(182) => 'Y',
            chr(225) . chr(187) . chr(183) => 'y',
            // tilde
            chr(225) . chr(186) . chr(170) => 'A',
            chr(225) . chr(186) . chr(171) => 'a',
            chr(225) . chr(186) . chr(180) => 'A',
            chr(225) . chr(186) . chr(181) => 'a',
            chr(225) . chr(186) . chr(188) => 'E',
            chr(225) . chr(186) . chr(189) => 'e',
            chr(225) . chr(187) . chr(132) => 'E',
            chr(225) . chr(187) . chr(133) => 'e',
            chr(225) . chr(187) . chr(150) => 'O',
            chr(225) . chr(187) . chr(151) => 'o',
            chr(225) . chr(187) . chr(160) => 'O',
            chr(225) . chr(187) . chr(161) => 'o',
            chr(225) . chr(187) . chr(174) => 'U',
            chr(225) . chr(187) . chr(175) => 'u',
            chr(225) . chr(187) . chr(184) => 'Y',
            chr(225) . chr(187) . chr(185) => 'y',
            // acute accent
            chr(225) . chr(186) . chr(164) => 'A',
            chr(225) . chr(186) . chr(165) => 'a',
            chr(225) . chr(186) . chr(174) => 'A',
            chr(225) . chr(186) . chr(175) => 'a',
            chr(225) . chr(186) . chr(190) => 'E',
            chr(225) . chr(186) . chr(191) => 'e',
            chr(225) . chr(187) . chr(144) => 'O',
            chr(225) . chr(187) . chr(145) => 'o',
            chr(225) . chr(187) . chr(154) => 'O',
            chr(225) . chr(187) . chr(155) => 'o',
            chr(225) . chr(187) . chr(168) => 'U',
            chr(225) . chr(187) . chr(169) => 'u',
            // dot below
            chr(225) . chr(186) . chr(160) => 'A',
            chr(225) . chr(186) . chr(161) => 'a',
            chr(225) . chr(186) . chr(172) => 'A',
            chr(225) . chr(186) . chr(173) => 'a',
            chr(225) . chr(186) . chr(182) => 'A',
            chr(225) . chr(186) . chr(183) => 'a',
            chr(225) . chr(186) . chr(184) => 'E',
            chr(225) . chr(186) . chr(185) => 'e',
            chr(225) . chr(187) . chr(134) => 'E',
            chr(225) . chr(187) . chr(135) => 'e',
            chr(225) . chr(187) . chr(138) => 'I',
            chr(225) . chr(187) . chr(139) => 'i',
            chr(225) . chr(187) . chr(140) => 'O',
            chr(225) . chr(187) . chr(141) => 'o',
            chr(225) . chr(187) . chr(152) => 'O',
            chr(225) . chr(187) . chr(153) => 'o',
            chr(225) . chr(187) . chr(162) => 'O',
            chr(225) . chr(187) . chr(163) => 'o',
            chr(225) . chr(187) . chr(164) => 'U',
            chr(225) . chr(187) . chr(165) => 'u',
            chr(225) . chr(187) . chr(176) => 'U',
            chr(225) . chr(187) . chr(177) => 'u',
            chr(225) . chr(187) . chr(180) => 'Y',
            chr(225) . chr(187) . chr(181) => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            chr(201) . chr(145) => 'a',
            // macron
            chr(199) . chr(149) => 'U',
            chr(199) . chr(150) => 'u',
            // acute accent
            chr(199) . chr(151) => 'U',
            chr(199) . chr(152) => 'u',
            // caron
            chr(199) . chr(141) => 'A',
            chr(199) . chr(142) => 'a',
            chr(199) . chr(143) => 'I',
            chr(199) . chr(144) => 'i',
            chr(199) . chr(145) => 'O',
            chr(199) . chr(146) => 'o',
            chr(199) . chr(147) => 'U',
            chr(199) . chr(148) => 'u',
            chr(199) . chr(153) => 'U',
            chr(199) . chr(154) => 'u',
            // grave accent
            chr(199) . chr(155) => 'U',
            chr(199) . chr(156) => 'u'
        );

        // Used for locale-specific rules
        if ('de_DE' == $locale) {
            $chars[chr(195) . chr(132)] = 'Ae';
            $chars[chr(195) . chr(164)] = 'ae';
            $chars[chr(195) . chr(150)] = 'Oe';
            $chars[chr(195) . chr(182)] = 'oe';
            $chars[chr(195) . chr(156)] = 'Ue';
            $chars[chr(195) . chr(188)] = 'ue';
            $chars[chr(195) . chr(159)] = 'ss';
        } elseif ('da_DK' === $locale) {
            $chars[chr(195) . chr(134)] = 'Ae';
            $chars[chr(195) . chr(166)] = 'ae';
            $chars[chr(195) . chr(152)] = 'Oe';
            $chars[chr(195) . chr(184)] = 'oe';
            $chars[chr(195) . chr(133)] = 'Aa';
            $chars[chr(195) . chr(165)] = 'aa';
        }

        return strtr($string, $chars);
    }
}
