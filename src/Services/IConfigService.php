<?php

namespace chsxf\MFX\Services;

use chsxf\MFX\Config;

interface IConfigService
{
    final public const DEFAULT_DOMAIN = '__default';

    /**
     * Loads configuration properties
     * @param Config $configData Config data
     * @param string $domain Domain name
     */
    public function load(Config $configData, string $domain = self::DEFAULT_DOMAIN);

    public function tryGetValue(string $property, mixed &$outValue, ?string $domain = null): bool;

    /**
     * Gets the value of a configuration property
     *
     * @param string $property Name of the propery
     * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
     * @return mixed
     */
    public function getValue(string $property, mixed $default = null, ?string $domain = null): mixed;

    /**
     * Determines if a configuration property has been provided in the configuration file
     *
     * @param string $property Name of the propery
     * @return boolean true if the property has been provided, false either
     */
    public function hasValue(string $property, ?string $domain = null): bool;
}
