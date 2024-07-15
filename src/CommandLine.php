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
final class CommandLine
{
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
    public static function handleInvocation(): ?array
    {
        global $argv;

        if (!self::isCLI()) {
            return null;
        }

        $arguments = array_slice($argv, 1);
        $argumentCount = count($arguments);

        $configFilePath = null;
        $route = '/';

        for ($i = 0; $i < $argumentCount; $i++) {
            $opt = $arguments[$i];

            if (preg_match('/^-/', $opt)) {
                switch ($opt) {
                    case '--config':
                        if ($i >= $argumentCount - 1) {
                            self::dieUsage('Missing configuration file path');
                        }
                        $configFilePath = $arguments[++$i];
                        break;

                    default:
                        self::dieUsage("Invalid option {$opt}");
                }
            } else {
                $route = "/{$opt}";
                break;
            }
        }

        return [$configFilePath, $route];
    }

    /**
     * Terminates the script when incorrectedly used and display the usage message
     */
    private static function dieUsage(?string $message = null): never
    {
        if ($message != null) {
            printf("%s\n\n", $message);
        }
        printf("Usage: php /path/to/mfx/entrypoint.php [options] [route]\n\n");
        printf("\t--config <file>\t\tPath to custom config file\n");
        printf("\n");
        exit();
    }
}
