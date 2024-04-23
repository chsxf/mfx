<?php

/**
 * Configuration management
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use ErrorException;

/**
 * Singleton configuration management helper class
 * 
 * @since 1.0
 */
class Config
{
	private const DOMAIN_NAME_REGEX = '/^[a-z0-9_]+$/i';
	private const PROPERTY_PATH_REGEX = '/^[a-z0-9_]+(?:\.[a-z0-9_]+)*$/i';

	/**
	 * @var Config Singleton reference
	 */
	private static ?Config $singleInstance = NULL;

	/**
	 * @var string Domain used when loading the next configuration file
	 */
	private static ?string $nextLoadDomain = NULL;

	/**
	 * @var array Configuration properties container
	 */
	private array $configData;

	/**
	 * Constructor
	 * 
	 * @param array $configData Config data
	 */
	private function __construct(array $configData = array())
	{
		$this->configData = $configData;
	}

	/**
	 * Loads configuration properties
	 *
	 * @since 1.0
	 * 
	 * @param array $configData Config data
	 */
	public static function load(array $configData = array())
	{
		if (self::$singleInstance === NULL) {
			if (self::$nextLoadDomain !== NULL) {
				throw new ErrorException("You can't load a config file on a domain before the main configuration file");
			}

			self::$singleInstance = new Config($configData);
		} else if (self::$nextLoadDomain !== NULL) {
			$nextDomain = self::$nextLoadDomain;
			self::$nextLoadDomain = NULL;

			if (array_key_exists($nextDomain, self::$singleInstance->configData)) {
				throw new ErrorException("The domain '{$nextDomain}' already exists");
			}

			self::$singleInstance->configData[$nextDomain] = $configData;
		} else {
			throw new ErrorException("The main configuration file has already been loaded");
		}
	}

	/**
	 * Loads configuration properties from a second configuration file into the specific domain
	 *
	 * @since 1.0
	 * 
	 * @param string $_domain Domain under which configuration properties will be set
	 * @param string $_path Path of the configuration file to load
	 */
	public static function loadOnDomain(string $_domain, string $_path)
	{
		if (!preg_match(self::DOMAIN_NAME_REGEX, $_domain)) {
			throw new ErrorException("'{$_domain}' is not a valid domain name");
		}

		self::$nextLoadDomain = $_domain;
		require($_path);
	}

	/**
	 * Gets the value of a configuration property
	 *
	 * @since 1.0
	 * 
	 * @param string $property Name of the propery
	 * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
	 * @throws ErrorException If the Config::load() function has not been executed at least once before
	 * @return mixed
	 */
	public static function get(string $property, mixed $default = NULL): mixed
	{
		if (self::$singleInstance === null) {
			throw new ErrorException("Config is not loaded.");
		}
		return self::$singleInstance->getProperty($property, $default);
	}

	/**
	 * Determines if a configuration property has been provided in the configuration file
	 *
	 * @since 1.0
	 * 
	 * @param string $property Name of the propery
	 * @throws ErrorException If the Config::load() function has not been executed at least once before
	 * @return boolean true if the property has been provided, false either
	 */
	public static function has(string $property): bool
	{
		if (self::$singleInstance === null) {
			throw new ErrorException("Config is not loaded.");
		}
		return self::$singleInstance->hasProperty($property);
	}

	/**
	 * Gets the value of a configuration property
	 *
	 * @param string $property Name of the propery
	 * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
	 * @return mixed
	 */
	private function getProperty(string $property, mixed $default = NULL): mixed
	{
		if (!preg_match(self::PROPERTY_PATH_REGEX, $property)) {
			throw new ErrorException("'{$property}' is not a valid property path");
		}

		$members = explode('.', $property);
		$arr = $this->configData;
		foreach ($members as $m) {
			if (!array_key_exists($m, $arr)) {
				return $default;
			}
			$arr = $arr[$m];
		}
		return $arr;
	}

	/**
	 * Determines if a configuration property has been provided in the configuration file
	 *
	 * @param string $property Name of the propery
	 * @return boolean true if the property has been provided, false either
	 */
	private function hasProperty(string $property): bool
	{
		if (!preg_match(self::PROPERTY_PATH_REGEX, $property)) {
			throw new ErrorException("'{$property}' is not a valid property path");
		}

		$members = explode('.', $property);
		$arr = $this->configData;
		foreach ($members as $m) {
			if (!array_key_exists($m, $arr)) {
				return false;
			}
			$arr = $arr[$m];
		}
		return true;
	}
}
