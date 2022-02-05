<?php
/**
 * Command-line invocation management
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Command-line invocation handling class.
 * Eases the use of the framework with command-line scripts
 */
class CommandLine
{
	/**
	 * @var array Arguments list
	 */
	private static array $_argv;
	/**
	 * @var int Argument count
	 */
	private static int $_argc;
	/**
	 * @var int Argument current index
	 */
	private static int $_argi;
	
	/**
	 * Tells if PHP is running on the command-line interface server API
	 * @return boolean true is the current server API is the command-line interface, false either
	 */
	public static function isCLI(): bool {
		return (PHP_SAPI == 'cli');
	}
	
	/**
	 * Handles command-line invocation and the parsing of MicroFX specific options from the arguments list
	 */
	public static function handleInvocation() {
        if (!self::isCLI()) {
            return;
        }
		
		self::_initArgs();
		
		// Options
		while (self::_hasArgument()) {
			$opt = self::_getNextArgument();
			
			if (preg_match('/^-/', $opt)) {
				switch ($opt) {
					case '--config':
						define('MFX_CONFIG_FILE_PATH', self::_getNextArgument());
						break;
						
					default:
						self::_dieUsage();
				}
			}
			else {
				$_SERVER['REQUEST_URI'] = "{$_SERVER['PHP_SELF']}/{$opt}";
				break;
			}
		}
	}
	
	/**
	 * Initializes the arguments list from the global $argc and $argv variables
	 */
	private static function _initArgs() {
		global $argv;
		self::$_argv = array_slice($argv, 1);
		self::$_argc = count(self::$_argv);
		self::$_argi = 0;
	}
	
	/**
	 * Tells if the arguments list contains further argument
	 * @return boolean
	 */
	private static function _hasArgument(): bool {
		return (self::$_argi < self::$_argc);
	}
	
	/**
	 * Retrieves the next argument in the list
	 * @return string
	 */
	private static function _getNextArgument(): string {
        if (!self::_hasArgument()) {
            self::_dieUsage();
        }
		return self::$_argv[self::$_argi++];
	}
	
	/**
	 * Terminates the script when incorrectedly used and display the usage message
	 */
	private static function _dieUsage() {
		printf("Usage: php -f /path/to/mfx/entrypoint.php [-- [options] [route]]\n\n");
		printf("\t--config <file>\t\tPath to custom config file\n");
		printf("\n");
		exit();
	}
}