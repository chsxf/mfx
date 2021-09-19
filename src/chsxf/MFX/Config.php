<?php
/**
 * Configuration management
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */
namespace chsxf\MFX;

use ErrorException;

/**
 * Singleton configuration management helper class
 */
class Config {

	/**
	 * @var Config Singleton reference
	 */
	private static $singleInstance = NULL;

	/**
	 * @var string Domain used when loading the next configuration file
	 */
	private static $nextLoadDomain = NULL;

	/**
	 * @var array Configuration properties container
	 */
	private $configData;

	/**
	 * Constructor
	 *
	 * @param array $configData Config data
	 */
	public function __construct(array $configData = array()) {
		$this->configData = $configData;
	}

	/**
	 * Loads configuration properties
	 *
	 * @param array $configData Config data
	 */
	public static function load(array $configData = array()) {
		if (self::$singleInstance === NULL) {
			self::$singleInstance = new Config($configData);
		}
		else if (self::$nextLoadDomain !== NULL) {
			$nextDomain = self::$nextLoadDomain;
			self::$nextLoadDomain = NULL;
			self::$singleInstance->configData[$nextDomain] = $configData;
		}
	}

	/**
	 * Loads configuration properties from a second configuration file into the specific domain
	 *
	 * @param string $_domain Domain under which configuration properties will be set
	 * @param string $_path Path of the configuration file to load
	 */
	public static function loadOnDomain(string $_domain, string $_path) {
		self::$nextLoadDomain = $_domain;
		require ($_path);
	}

	/**
	 * Gets the value of a configuration property
	 *
	 * @param string $property Name of the propery
	 * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
	 * @throws ErrorException If the Config::load() function has not been executed at least once before
	 * @return mixed
	 */
	public static function get($property, $default = NULL) {
		if (self::$singleInstance === NULL)
			throw new ErrorException("Config is not loaded.");
		return self::$singleInstance->getProperty($property, $default);
	}

	/**
	 * Determines if a configuration property has been provided in the configuration file
	 *
	 * @param string $property Name of the propery
	 * @throws ErrorException If the Config::load() function has not been executed at least once before
	 * @return boolean true if the property has been provided, false either
	 */
	public static function has($property) {
		if (self::$singleInstance === NULL)
			throw new ErrorException("Config is not loaded.");
		return self::$singleInstance->hasProperty($property);
	}

	/**
	 * Gets the value of a configuration property
	 *
	 * @param string $property Name of the propery
	 * @param mixed $default Default value if the property has not been provided (Defaults to NULL)
	 * @return mixed
	 */
	public function getProperty($property, $default = NULL) {
		$property = trim($property);
		if (empty($property))
			return false;

		$members = explode('.', $property);
		$arr = $this->configData;
		foreach ($members as $m) {
			if (!array_key_exists($m, $arr))
				return $default;
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
	public function hasProperty($property) {
		$property = trim($property);
		if (empty($property))
			return false;

		$members = explode('.', $property);
		$arr = $this->configData;
		foreach ($members as $m) {
			if (!array_key_exists($m, $arr))
				return false;
			$arr = $arr[$m];
		}
		return true;
	}

	/**
	 * Tells if the current runtime is Google App Engine
	 *
	 * @return boolean
	 */
	public static function isGoogleAppEngineRuntime() {
		return isset($_ENV['APPENGINE_RUNTIME']);
	}

}