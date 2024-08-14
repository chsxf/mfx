<?php

declare(strict_types=1);

namespace chsxf\MFX\Services;

use chsxf\MFX\Config;

/**
 * Configuration service interface
 * @since 2.0
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
interface IConfigService
{
    final public const DEFAULT_DOMAIN = '__default';

    /**
     * Loads configuration properties
     * @param Config $configData Config data
     * @param string $domain Domain name
     */
    public function load(Config $configData, string $domain = self::DEFAULT_DOMAIN);

    /**
     * Tries to get the value of a configuration property
     * @param string $property Name of the propery
     * @param mixed $outValue Reference to the variable containing the property value if existing
     * @param string|null $domain Domain name (defaults to NULL, which is the default domain)
     * @return bool true if the property exists in the loaded configuration, or false either
     */
    public function tryGetValue(string $property, mixed &$outValue, ?string $domain = null): bool;

    /**
     * Gets the value of a configuration property
     * @param string $property Name of the propery
     * @param mixed $default Default value if the property does not exist in the loaded configuration (Defaults to NULL)
     * @param string|null $domain Domain name (defaults to NULL, which is the default domain)
     * @return mixed
     */
    public function getValue(string $property, mixed $default = null, ?string $domain = null): mixed;

    /**
     * Determines if a property has been loaded in the configuration
     * @param string $property Name of the propery
     * @param string|null $domain Domain name (defaults to NULL, which is the default domain)
     * @return bool true if the property exists in the loaded configuration, false either
     */
    public function hasValue(string $property, ?string $domain = null): bool;
}
