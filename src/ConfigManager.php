<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\ConfigException;
use chsxf\MFX\Services\IConfigService;

/**
 * Configuration directives manager, acting as the default configuration service implementation
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
final class ConfigManager implements IConfigService
{
    private const DOMAIN_NAME_REGEX = '/^[a-z0-9_]+$/i';
    private const PROPERTY_PATH_REGEX = '/^[a-z0-9_]+(?:\.[a-z0-9_]+)*$/i';

    /**
     * @var array Configuration properties container
     */
    private array $configDataByDomain;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configDataByDomain = [];
    }

    /**
     * Loads configuration properties
     *
     * @param Config $configData Config data
     * @param string $domain Domain name
     */
    public function load(Config $configData, string $domain = self::DEFAULT_DOMAIN)
    {
        $trimmedDomain = trim($domain);

        if (!preg_match(self::DOMAIN_NAME_REGEX, $trimmedDomain)) {
            throw new ConfigException("The domain '{$trimmedDomain}' is invalid");
        }
        if (array_key_exists($trimmedDomain, $this->configDataByDomain)) {
            throw new ConfigException("The domain '{$trimmedDomain}' has already been loaded");
        }

        $this->configDataByDomain[$trimmedDomain] = $configData;
    }

    /**
     * Try getting a value from the currently loaded configuration directives
     * @param string $property Path of the property we're trying to get the value of
     * @param mixed $outValue Output value reference
     * @param null|string $domain Domain name (defaults to null, therefore using the default domain)
     * @return bool <code>true</code> if the value exists, <code>false</code> either
     * @throws ConfigException if the requested domain is not loaded, or the property path or the domain uses invalid syntax
     */
    public function tryGetValue(string $property, mixed &$outValue, ?string $domain = null): bool
    {
        if ($domain === null) {
            $trimmedDomain = self::DEFAULT_DOMAIN;
        } else {
            $trimmedDomain = trim($domain);

            if (!preg_match(self::DOMAIN_NAME_REGEX, $domain)) {
                throw new ConfigException("'{$trimmedDomain}' is not a valid domain");
            }
        }

        if (!preg_match(self::PROPERTY_PATH_REGEX, $property)) {
            throw new ConfigException("'{$property}' is not a valid property path");
        }
        if (!array_key_exists($trimmedDomain, $this->configDataByDomain)) {
            throw new ConfigException("Domain '{$trimmedDomain}' has not been loaded");
        }

        $members = explode('.', $property);
        $arr = $this->configDataByDomain[$trimmedDomain]->data;
        foreach ($members as $m) {
            if (!array_key_exists($m, $arr)) {
                if (count($members) > 1 && $domain === null) {
                    $otherDomain = $members[0];
                    if (array_key_exists($otherDomain, $this->configDataByDomain)) {
                        $otherProperty = implode('.', array_slice($members, 1));
                        return $this->tryGetValue($otherProperty, $outValue, $otherDomain);
                    }
                }

                $outValue = null;
                return false;
            }
            $arr = $arr[$m];
        }
        $outValue = $arr;
        return true;
    }

    /**
     * Gets the value of a configuration property
     *
     * @param string $property Path of the property we're trying to get the value of
     * @param mixed $default Default value if the property has not been found (Defaults to NULL)
     * @param null|string $domain Domain name (defaults to null, therefore using the default domain)
     * @return mixed
     */
    public function getValue(string $property, mixed $default = null, ?string $domain = null): mixed
    {
        return $this->tryGetValue($property, $outValue, $domain) ? $outValue : $default;
    }

    /**
     * Determines if a configuration property has been provided in the configuration file
     *
     * @param string $property Path of the property we're trying to get the value of
     * @param null|string $domain Domain name (defaults to null, therefore using the default domain)
     * @return boolean true if the property has been provided, false either
     */
    public function hasValue(string $property, ?string $domain = null): bool
    {
        return $this->tryGetValue($property, $_, $domain);
    }
}
