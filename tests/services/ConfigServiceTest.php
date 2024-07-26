<?php

use chsxf\MFX\Config;
use chsxf\MFX\ConfigManager;
use chsxf\MFX\Exceptions\ConfigException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigServiceTest extends TestCase
{
    private const TEST_VALUE_KEY = 'testValue';
    private const TEST_GROUP_KEY = 'testGroup';
    private const TEST_INNER_VALUE_KEY = 'testInnerValue';

    private const TEST_VALUE = 42;
    private const TEST_INNER_VALUE = 'test_inner_value';

    private const ALTERNATIVE_DOMAIN = 'alter_domain';
    private const ALTERNATIVE_VALUE_KEY = 'alter_key';
    private const ALTERNATIVE_VALUE = 321;

    private const DEFAULT_VALUE = -1;

    private static ?ConfigManager $testConfigManager = null;

    public static function setUpBeforeClass(): void
    {
        $configData = new Config([
            self::TEST_VALUE_KEY => self::TEST_VALUE,

            self::TEST_GROUP_KEY => [
                self::TEST_INNER_VALUE_KEY => self::TEST_INNER_VALUE
            ]
        ]);

        $alternativeConfigData = new Config([
            self::ALTERNATIVE_VALUE_KEY => self::ALTERNATIVE_VALUE
        ]);

        self::$testConfigManager = new ConfigManager();
        self::$testConfigManager->load($configData);
        self::$testConfigManager->load($alternativeConfigData, self::ALTERNATIVE_DOMAIN);
    }

    public static function tearDownAfterClass(): void
    {
        self::$testConfigManager = null;
    }

    public static function valueProvider(): array
    {
        return [
            [self::TEST_VALUE_KEY, null, self::TEST_VALUE, true],
            [self::TEST_GROUP_KEY, null, null, true],
            [implode('.', [self::TEST_GROUP_KEY, self::TEST_INNER_VALUE_KEY]), null, self::TEST_INNER_VALUE, true],
            [self::TEST_INNER_VALUE_KEY, null, null, false],
            [self::ALTERNATIVE_VALUE_KEY, null, null, false],
            [self::ALTERNATIVE_VALUE_KEY, self::ALTERNATIVE_DOMAIN, self::ALTERNATIVE_VALUE, true],
            [implode('.', [self::ALTERNATIVE_DOMAIN, self::ALTERNATIVE_VALUE_KEY]), null, self::ALTERNATIVE_VALUE, true]
        ];
    }

    #[Test, DataProvider('valueProvider')]
    public function hasValue(string $property, ?string $domain, mixed $expectedValue, bool $expectedResult): void
    {
        $result = self::$testConfigManager->hasValue($property, $domain);
        $this->assertSame($expectedResult, $result, "Configuration has no property named {$property} in any domain");
    }

    #[Test, DataProvider('valueProvider')]
    public function tryGetValue(string $property, ?string $domain, mixed $expectedValue, bool $expectedResult): void
    {
        if ($expectedValue === null) {
            $this->markTestSkipped();
        } else {
            $result = self::$testConfigManager->tryGetValue($property, $value, $domain);
            $this->assertSame($expectedResult, $result);
            $this->assertSame($expectedValue, $value);
        }
    }

    #[Test, DataProvider('valueProvider')]
    public function getValue(string $property, ?string $domain, mixed $expectedValue, bool $expectedResult): void
    {
        $value = self::$testConfigManager->getValue($property, self::DEFAULT_VALUE, $domain);
        if (!$expectedResult) {
            $this->assertSame($value, self::DEFAULT_VALUE);
        } else if ($expectedValue === null) {
            $this->markTestSkipped();
        } else {
            $this->assertSame($value, $expectedValue);
        }
    }

    #[Test]
    public function testDomainRegex()
    {
        $this->expectException(ConfigException::class);
        self::$testConfigManager->tryGetValue('test', $_, '!!');
    }

    #[Test]
    public function testPropertyRegex()
    {
        $this->expectException(ConfigException::class);
        self::$testConfigManager->tryGetValue('Invalid-Property-Path', $_);
    }
}
