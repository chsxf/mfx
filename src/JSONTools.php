<?php

/**
 * JSON tools
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Class containing utility functions for encoding data in JSON
 * @since 1.0
 */
class JSONTools
{
    /**
     * @var string Stores the string representation of PHP_INT_MAX
     */
    private static ?string $PHP_INT_MAX_AS_STR = null;
    /**
     * @var int Stores the string representation length of PHP_INT_MAX
     */
    private static int $PHP_INT_MAX_LENGTH = 0;
    /**
     * @var array Container for object references used to avoid recursions
     */
    private static array $RECURSIONS;

    /**
     * Filter string values recursively in order to use the proper primitive type if applying.
     *
     * For objects, the function proceeds to a clone and iterates only on public properties.
     *
     * @since 1.0
     *
     * @param mixed $var Value to filter
     * @return mixed The filtered value, or the original value if no filtering is applying
     *
     * @used-by JSONTools::filterAndEncode()
     */
    private static function filter(mixed $var): mixed
    {
        // Scalar values
        if (is_scalar($var)) {
            if (is_string($var)) {
                $regs = null;

                // Booleans as string
                if (preg_match('/^(true|false)$/', $var)) {
                    return ($var === 'true');
                }
                // Integers as string
                elseif (preg_match('/^-?([1-9]\d*)$/', $var, $regs)) {
                    if (self::$PHP_INT_MAX_AS_STR === null) {
                        self::$PHP_INT_MAX_AS_STR = strval(PHP_INT_MAX);
                        self::$PHP_INT_MAX_LENGTH = strlen(self::$PHP_INT_MAX_AS_STR);
                    }

                    $length = strlen($regs[1]);
                    if ($length < self::$PHP_INT_MAX_LENGTH || ($length == self::$PHP_INT_MAX_LENGTH && strcmp(self::$PHP_INT_MAX_AS_STR, $regs[1]) >= 0)) {
                        return intval($var);
                    } else {
                        return $var;
                    }
                } elseif (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $var)) {
                    return floatval($var);
                } else {
                    return $var;
                }
            } else {
                return $var;
            }
        }
        // Arrays
        elseif (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::filter($v);
            }
            return $var;
        }
        // Objects
        elseif (is_object($var)) {
            // Check for recursions
            if (in_array($var, self::$RECURSIONS)) {
                return '#RECURSION#';
            }
            self::$RECURSIONS[] = $var;

            if ($var instanceof IUnfilteredSerializable == false) {
                $newObj = clone $var;
                $ro = new \ReflectionObject($newObj);
                $props = $ro->getProperties(\ReflectionProperty::IS_PUBLIC);
                foreach ($props as $v) {
                    if ($v->isDefault()) {
                        $v->setValue($newObj, self::filter($v->getValue($newObj)));
                    } else {
                        $name = $v->getName();
                        $newObj->$name = self::filter($newObj->$name);
                    }
                }
                $res = $newObj;
            } else {
                $res = $var;
            }

            array_pop(self::$RECURSIONS);
            return $res;
        }
        // Other
        else {
            return $var;
        }
    }

    /**
     * Filter the specified value and encode it in JSON
     *
     * @since 1.0
     *
     * @param mixed $var Value to filter and encode
     * @return string A JSON-encoded string representing the specified value
     *
     * @uses JSONTools::filter() to filter the value
     */
    public static function filterAndEncode(mixed $var): string
    {
        self::$RECURSIONS = array();
        return json_encode(self::filter($var));
    }
}
