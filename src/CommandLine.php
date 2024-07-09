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
 *
 * @since 1.0
 */
class CommandLine
{
    /**
     * @var array Arguments list
     */
    private static array $argv;
    /**
     * @var int Argument count
     */
    private static int $argc;
    /**
     * @var int Argument current index
     */
    private static int $argi;

    /**
     * Tells if PHP is running on the command-line interface (CLI) server API
     *
     * @since 1.0
     *
     * @return boolean true is the current server API is the command-line interface, false either
     */
    public static function isCLI(): bool
    {
        return (PHP_SAPI == 'cli');
    }

    /**
     * Handles command-line invocation and the parsing of MicroFX specific options from the arguments list
     * @ignore
     */
    public static function handleInvocation()
    {
        if (!self::isCLI()) {
            return;
        }

        self::initArgs();

        // Options
        while (self::hasArgument()) {
            $opt = self::getNextArgument();

            if (preg_match('/^-/', $opt)) {
                switch ($opt) {
                    case '--config':
                        define('MFX_CONFIG_FILE_PATH', self::getNextArgument());
                        break;

                    default:
                        self::dieUsage();
                }
            } else {
                $_SERVER['REQUEST_URI'] = "{$_SERVER['PHP_SELF']}/{$opt}";
                break;
            }
        }
    }

    /**
     * Initializes the arguments list from the global $argc and $argv variables
     */
    private static function initArgs()
    {
        global $argv;
        self::$argv = array_slice($argv, 1);
        self::$argc = count(self::$argv);
        self::$argi = 0;
    }

    /**
     * Tells if the arguments list contains further argument
     * @return boolean
     */
    private static function hasArgument(): bool
    {
        return (self::$argi < self::$argc);
    }

    /**
     * Retrieves the next argument in the list
     * @return string
     */
    private static function getNextArgument(): string
    {
        if (!self::hasArgument()) {
            self::dieUsage();
        }
        return self::$argv[self::$argi++];
    }

    /**
     * Terminates the script when incorrectedly used and display the usage message
     */
    private static function dieUsage()
    {
        printf("Usage: php /path/to/mfx/entrypoint.php [options] [route]\n\n");
        printf("\t--config <file>\t\tPath to custom config file\n");
        printf("\n");
        exit();
    }
}
