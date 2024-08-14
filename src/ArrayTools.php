<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Array helpers
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class ArrayTools
{
    /**
     * Reverses dimensions of the source array.
     *
     * The result array is built using the keys of the contained arrays, each referencing a new array.
     * Each related row value is then added in the corresponding array.
     *
     * This function is useful to convert database rows to HTML form data.
     *
     * Note:
     * It is assumed that all contained arrays use the same keys.
     *
     * @param array $store Source array
     * @return array
     */
    public static function reverseArrays(array $store): array
    {
        if (empty($store)) {
            return array();
        }

        $keys = array_keys(reset($store));
        $result = array();
        foreach ($keys as $k) {
            $result[$k] = array();
        }
        foreach ($store as $row) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $result)) {
                    $result[$k][] = $row[$k];
                } else {
                    $result[$k][] = null;
                }
            }
        }
        return $result;
    }

    /**
     * Concatenate values together into a new array without considering keys or types.
     *
     * The function accepts unlimited arguments.
     * However, if a single array argument is passed, it is used as an array of arguments, thus its content will be concatenated and the array itself.
     *
     * @return array
     */
    public static function concatArrays(mixed ...$arguments): array
    {
        $result = array();

        $args = $arguments;
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $result = array_merge($result, $arg);
            } else {
                $result[] = $arg;
            }
        }

        return $result;
    }

    /**
     * Shuffles the content of an array
     *
     * @param array $arr
     */
    public static function shuffle(array &$arr)
    {
        uasort($arr, function ($a, $b) {
            return mt_rand(-1, 1);
        });
    }

    /**
     * Checks if the parameter is an array or a union type accepting an array
     *
     * @param \ReflectionParameter $parameter The parameter to investigate
     * @return bool
     */
    public static function isParameterArray(\ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if ($type !== null) {
            if ($type instanceof \ReflectionNamedType) {
                return $type->getName() === 'array';
            } else {
                foreach ($type->getTypes() as $unionType) {
                    if ($unionType->getName() === 'array') {
                        return true;
                    }
                }
                return false;
            }
        }
    }
}
