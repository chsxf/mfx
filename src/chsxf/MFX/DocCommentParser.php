<?php
/**
 * Documentation comment parser
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 *
 * @license GPL version 2
 */

namespace chsxf\MFX;

/**
 * Code for an exception thrown when a wrong reflector is passed to DocCommentParser::parse()
 * @link DocCommentParser::parse()
 */
define('DCPE_WRONG_REFLECTOR_CODE', 1);
/**
 * Code for an exception thrown when an unknown function name is passed to DocCommentParser::parseFromFunction()
 * @link DocCommentParser::parseFromFunction()
 */
define('DCPE_UNKNOWN_FUNCTION_CODE', 2);
/**
 * Code for an exception thrown when an unknown class name is passed to DocCommentParser::parseFromClass()
 * @link DocCommentParser::parseFromClass()
 */
define('DCPE_UNKNOWN_CLASS_CODE', 3);
/**
 * Code for an exception thrown when an unknown method class name is passed to DocCommentParser::parseFromClassMethod()
 * @link DocCommentParser::parseFromClassMethod()
 */
define('DCPE_UNKNOWN_CLASS_METHOD_CODE', 4);

/**
 * Exception class for exceptions thrown by the DocCommentParser class
 *
 * @link DocCommentParser
 */
class DocCommentParserException extends \ErrorException
{
	/**
	 * Constructor
	 *
	 * @param string $message Message
	 * @param int $code Code
	 * @param string $filename Name of the file where the exception was thrown
	 * @param int $lineno Line number where the exception was thrown
	 */
	public function __construct(string $message, int $code, string $filename, int $lineno) {
		parent::__construct($message, 0, 0, $filename, $lineno);
	}
}

/**
 * Default parser for documentation comment
 */
class DocCommentParser
{
	/**
	 * @var array List of regular expressions used to filter valid parameters from comment
	 */
	private ?array $_validParametersRegularExpressions = NULL;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_validParametersRegularExpressions = array();
		$this->addValidParametersRegularExpression('/^mfx_/');

		$additionalPrefixes = Config::get('doccommentparser.prefixes', array());
		foreach ($additionalPrefixes as $prefix) {
			$this->addValidParametersRegularExpression("/^{$prefix}/");
		}
	}

	/**
	 * Add a regular expression to the list of valid parameter filters
	 *
	 * @param string $regexp Regular expression. Syntax follows preg_match's ones.
	 * @see preg_match()
	 */
	public final function addValidParametersRegularExpression(string $regexp) {
        if (is_array($this->_validParametersRegularExpressions) && !empty($regexp)) {
            $this->_validParametersRegularExpressions[] = $regexp;
        }
	}

	/**
	 * Parse valid parameters from documentation comment
	 *
	 * Valid parameters are returned in an associative array,
	 * whose keys are parameter name and values contains an arbitrary string.
	 *
	 * At this time, all parameter values are stored as string as-is.
	 *
	 * Example with a parameter value:
	 * <code>@valid_parameter_name parameter_value</code>
	 * will result in
	 * <code>$returned_array['valid_parameter_name'] = 'parameter_value';</code>
	 *
	 * Example without value:
	 * <code>@valid_parameter_name</code>
	 * will result in
	 * <code>$returned_array['valid_parameter_name'] = true;</code>
	 *
	 * @param \Reflector $reflector Reflector object identifying the langage element to use as a reference
	 * @return array An associative array listing all valid parameters
	 */
	public final function parse(\ReflectionClass|\ReflectionFunctionAbstract $reflector): array {
        if ($reflector instanceof \ReflectionFunctionAbstract == false && $reflector instanceof \ReflectionClass == false) {
            throw new DocCommentParserException(
                "The provided Reflector is not an instance of ReflectionFunction, ReflectionMethod or ReflectionClass",
                DCPE_WRONG_REFLECTOR_CODE,
                __FILE__,
                __LINE__
            );
        }

		$docComment = $reflector->getDocComment();

		$lines = preg_split("/\r|\n/", $docComment);
		$valid_parameters = array();
		foreach ($lines as $line) {
			$line = trim($line);
            if (preg_match('#^/\*\*#', $line) || preg_match('#\*/$#', $line)) {
                continue;
            }
			$line = ltrim($line, '* ');
            if (empty($line) || !preg_match('/^@/', $line)) {
                continue;
            }
			$line = ltrim($line, '@');

			foreach ($this->_validParametersRegularExpressions as $regexp) {
				if (preg_match($regexp, $line)) {
					$chunks = explode(' ', $line, 2);
                    if (count($chunks) == 1) {
                        $valid_parameters[$chunks[0]] = true;
                    }
					else {
                        $valid_parameters[$chunks[0]] = $chunks[1];
                    }
					break;
				}
			}
		}
		return $valid_parameters;
	}

	/**
	 * Convenience function to parse valid parameters from a function's documentation comment from its name
	 *
	 * @param string $function_name Name of the function
	 * @throws DocCommentParserException if the function is unknown
	 * @return array An associative array listing all valid parameters
	 *
	 * @see DocCommentParser::parse()
	 */
	public final function parseFromFunction(string $function_name): array {
		try {
			return $this->parse(new \ReflectionFunction($function_name));
		}
		catch (\ReflectionException $e) {
			throw new DocCommentParserException("Unknown function '{$function_name}'",
												DCPE_UNKNOWN_FUNCTION_CODE, __FILE__, __LINE__);
		}
	}

	/**
	 * Convenience function to parse valid parameters from a class's documentation comment from its name
	 *
	 * @param string $class_name Name of the class
	 * @throws DocCommentParserException if the class is unknown
	 * @return array An associative array listing all valid parameters
	 *
	 * @see DocCommentParser::parse()
	 */
	public final function parseFromClass(string $class_name): array {
		try {
			return $this->parse(new \ReflectionClass($class_name));
		}
		catch (\ReflectionException $e) {
			throw new DocCommentParserException("Unknown class '{$class_name}'",
												DCPE_UNKNOWN_CLASS_CODE, __FILE__, __LINE__);
		}
	}

	/**
	 * Convenience function to parse valid parameters from a class method's documentation comment from its name
	 *
	 * @param string $class_name Name of the class
	 * @param string $method_name Name of the method
	 * @throws DocCommentParserException if the class method is unknown
	 * @return array An associative array listing all valid parameters
	 *
	 * @see DocCommentParser::parse()
	 */
	public final function parseFromClassMethod(string $class_name, string $method_name): array {
		try {
			return $this->parse(new \ReflectionMethod($class_name, $method_name));
		}
		catch (\ReflectionException $e) {
			throw new DocCommentParserException("Unknown class method '{$class_name}::{$method_name}'",
												DCPE_UNKNOWN_CLASS_METHOD_CODE, __FILE__, __LINE__);
		}
	}
}
