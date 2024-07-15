<?php

namespace chsxf\MFX\Services;

use chsxf\MFX\Config;

interface IConfigService
{
    public final const DEFAULT_DOMAIN = '__default';

    /**
     * Loads configuration properties
     *
     * @since 1.0
     *
     * @param Config $configData Config data
     * @param string $domain Domain name
     */
    function load(Config $configData, string $domain = self::DEFAULT_DOMAIN);

    function tryGetValue(string $property, mixed &$outValue, ?string $domain = null): bool;

    /**
     * Gets the value of a configuration property
     *
     * @param string $property Name of the propery
     * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
     * @return mixed
     */
    function getValue(string $property, mixed $default = null, ?string $domain = null): mixed;

    /**
     * Determines if a configuration property has been provided in the configuration file
     *
     * @param string $property Name of the propery
     * @return boolean true if the property has been provided, false either
     */
    function hasValue(string $property, ?string $domain = null): bool;
}
