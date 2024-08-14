<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * Class containing utility functions for encoding data in XML
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
class XMLTools
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
     * Write XML tree from a variable
     *
     * For objects, the function iterates only on public properties.
     *
     * @param mixed $var Value to write as XML
     * @param boolean $filterStrings If set, the strings are filtered to the native primitive type if applying. (Defaults to true)
     *
     * @used-by XMLTools::build()
     */
    private static function recursiveBuild(\XMLWriter $writer, mixed $var, bool $filterStrings = true)
    {
        // NULL
        if (is_null($var)) {
            $writer->writeElement('null');
        }
        // Scalar values
        elseif (is_scalar($var)) {
            if (is_string($var)) {
                $regs = null;

                // Booleans as string
                if ($filterStrings && preg_match('/^(true|false)$/', $var)) {
                    $writer->writeElement('bool', $var);
                }
                // Integers as string
                elseif ($filterStrings && preg_match('/^-?([1-9]\d*)$/', $var, $regs)) {
                    if (self::$PHP_INT_MAX_AS_STR === null) {
                        self::$PHP_INT_MAX_AS_STR = strval(PHP_INT_MAX);
                        self::$PHP_INT_MAX_LENGTH = strlen(self::$PHP_INT_MAX_AS_STR);
                    }

                    $length = strlen($regs[1]);
                    if ($length < self::$PHP_INT_MAX_LENGTH || ($length == self::$PHP_INT_MAX_LENGTH && strcmp(self::$PHP_INT_MAX_AS_STR, $regs[1]) >= 0)) {
                        $writer->writeElement('int', $var);
                    } else {
                        $writer->startElement('string');
                        $writer->writeCdata($var);
                        $writer->endElement();
                    }
                } elseif ($filterStrings && preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $var)) {
                    $writer->writeElement('float', $var);
                } else {
                    $writer->startElement('string');
                    $writer->writeCdata($var);
                    $writer->endElement();
                }
            } elseif (is_int($var)) {
                $writer->writeElement('int', strval($var));
            } elseif (is_float($var)) {
                $writer->writeElement('float', strval($var));
            } elseif (is_bool($var)) {
                $writer->writeElement('bool', $var ? 'true' : 'false');
            }
        }
        // Arrays
        elseif (is_array($var)) {
            $writer->startElement('array');
            foreach ($var as $k => $v) {
                $writer->startElement('key');
                $writer->writeCdata(strval($k));
                $writer->endElement();
                self::recursiveBuild($writer, $v, $filterStrings);
            }
            $writer->endElement();
        }
        // Objects
        elseif (is_object($var)) {
            // Check for recursions
            if (in_array($var, self::$RECURSIONS)) {
                $writer->startElement('object');
                $writer->writeAttribute('recursion', 'true');
                $writer->endElement();
                return;
            }
            self::$RECURSIONS[] = $var;

            $filterStrings = ($filterStrings && $var instanceof IUnfilteredSerializable == false);

            $ro = new \ReflectionObject($var);
            $writer->startElement('object');
            if ($var instanceof \stdClass == false) {
                $writer->writeAttribute('class', $ro->name);
            }
            $props = $ro->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($props as $v) {
                $writer->startElement('prop');
                $writer->writeCdata($v->getName());
                $writer->endElement();
                self::recursiveBuild($writer, $v->getValue($var), $filterStrings);
            }
            $writer->endElement();

            array_pop(self::$RECURSIONS);
        }
    }

    /**
     * Build XML tree from a variable
     * @param mixed $var Variable from which building the XML tree
     * @param string $encoding Encoding charset (Defaults to UTF-8).
     * @return string the XML tree string
     */
    public static function build(mixed $var, string $encoding = 'UTF-8'): string
    {
        self::$RECURSIONS = array();

        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString("\t");
        $writer->startDocument('1.0', $encoding);
        $writer->startElement('root');
        self::recursiveBuild($writer, $var);
        $writer->endElement();
        $writer->endDocument();
        return $writer->outputMemory(false);
    }
}
