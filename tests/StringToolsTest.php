<?php

use chsxf\MFX\StringTools;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringToolsTest extends TestCase
{
    #[Test, DataProvider('emailAddressProvider')]
    public function isValidEmailAddress(string $testedAddress, bool $expectedResult): void
    {
        $expectedResultAsString = json_encode($expectedResult);
        $this->assertSame($expectedResult, StringTools::isValidEmailAddress($testedAddress), "Test failed for address {$testedAddress} - Should be {$expectedResultAsString}");
    }

    public static function emailAddressProvider(): array
    {
        // Source: https://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses

        return [
            // Valid email addresses
            ['email@example.com', true],
            ['firstname.lastname@example.com', true],
            ['email@subdomain.example.com', true],
            ['firstname+lastname@example.com', true],
            ['email@123.123.123.123', true],
            ['email@[123.123.123.123]', true],
            ['“email”@example.com', true],
            ['1234567890@example.com', true],
            ['email@example-one.com', true],
            ['_______@example.com', true],
            ['email@example.name', true],
            ['email@example.museum', true],
            ['email@example.co.jp', true],
            ['firstname-lastname@example.com', true],
            ['email@example.web', true],

            // Complex valid addresses
            ['much.“more\ unusual”@example.com', true],
            ['very.unusual.“@”.unusual.com@example.com', true],
            ['very.“(),:;<>[]”.VERY.“very@\\ "very”.unusual@strange.example.com', true],

            // List of invalid email addresses
            ['plainaddress', false],
            ['#@%^%#$@#$@#.com', false],
            ['@example.com', false],
            ['Joe Smith <email@example.com>', false],
            ['email.example.com', false],
            ['email@example@example.com', false],
            ['.email@example.com', false],
            ['email.@example.com', false],
            ['email..email@example.com', false],
            ['あいうえお@example.com', false],
            ['email@example.com (Joe Smith)', false],
            ['email@example', false],
            ['email@-example.com', false],
            ['email@111.222.333.44444', false],
            ['email@example..com', false],
            ['Abc..123@example.com', false],

            // Complex invalid addresses
            ['“(),:;<>[\]@example.com', false],
            ['just"not"right@example.com', false],
            ['this\ is"really"not\allowed@example.com', false]
        ];
    }

    #[Test, DataProvider('positiveIntegerProvider')]
    public function isPositiveInteger(string $str, bool $canBeZero, bool $expectedResult)
    {
        $this->assertSame($expectedResult, StringTools::isPositiveInteger($str, $canBeZero));
    }

    public static function positiveIntegerProvider(): array
    {
        return [
            ['1', false, true],
            ['1', true, true],
            ['0', false, false],
            ['0', true, true],
            ['-123', false, false],
            ['-123', true, false],
            ['a', false, false],
            ['true', false, false]
        ];
    }

    #[Test, DataProvider('negativeIntegerProvider')]
    public function isNegativeInteger(string $str, bool $canBeZero, bool $expectedResult)
    {
        $this->assertSame($expectedResult, StringTools::isNegativeInteger($str, $canBeZero));
    }

    public static function negativeIntegerProvider(): array
    {
        return [
            ['1', false, false],
            ['1', true, false],
            ['0', false, false],
            ['0', true, true],
            ['-0', false, false],
            ['-0', true, true],
            ['-123', false, true],
            ['-123', true, true],
            ['a', false, false],
            ['true', false, false]
        ];
    }

    #[Test, DataProvider('tntegerProvider')]
    public function isInteger(string $str, bool $expectedResult)
    {
        $this->assertSame($expectedResult, StringTools::isInteger($str));
    }

    public static function tntegerProvider(): array
    {
        return [
            ['1', true],
            ['1', true],
            ['0', true],
            ['0', true],
            ['-0', true],
            ['-0', true, true],
            ['-123', true],
            ['-123', true],
            ['a', false],
            ['true', false]
        ];
    }

    #[Test, DataProvider('randomStringProvider')]
    public function generateRandomString(?string $charset, int $length): void
    {
        if ($charset === NULL) {
            $generatedString = StringTools::generateRandomString($length);
            $expectedCharset = StringTools::CHARSET_ALPHANUMERIC_LC;
        } else {
            $generatedString = StringTools::generateRandomString($length, $charset);
            $expectedCharset = $charset;
        }

        $this->assertSame($length, strlen($generatedString));
        for ($i = 0; $i < $length; $i++) {
            $generatedChar = $generatedString[$i];
            $indexInCharset = strpos($expectedCharset, $generatedChar);
            $this->assertIsInt($indexInCharset);
        }
    }

    public static function randomStringProvider(): array
    {
        return [
            [StringTools::CHARSET_ALPHA_LC, 12],
            [StringTools::CHARSET_ALPHA_UC, 17],
            [StringTools::CHARSET_ALPHA_CI, 124],
            [StringTools::CHARSET_ALPHANUMERIC_LC, 32],
            [StringTools::CHARSET_ALPHANUMERIC_UC, 64],
            [StringTools::CHARSET_ALPHANUMERIC_CI, 96],
            [NULL, 47]
        ];
    }

    #[Test, DataProvider('implodeProvide')]
    public function implode(string $separator, array $elements, ?string $lastSeparator, ?string $firstSeparator, string $expectedResult): void
    {
        $implodedString = StringTools::implode($separator, $elements, $lastSeparator, $firstSeparator);
        $this->assertSame($expectedResult, $implodedString);
    }

    public static function implodeProvide(): array
    {
        return [
            [',', ['a', 'b', 'c'], NULL, NULL, 'a,b,c'],
            [', ', ['a', 'b', 'c', 'd'], NULL, ': ', 'a: b, c, d'],
            [', ', ['a', 'b', 'c', 'd'], ' = ', NULL, 'a, b, c = d'],
            [', ', ['a', 'b', 'c', 'd'], ' = ', ': ', 'a: b, c = d'],
            [', ', ['a'], ' = ', ': ', 'a'],
            [', ', [], ' = ', ': ', '']
        ];
    }

    #[Test, DataProvider('snakeToCamelCaseProvider')]
    public function snakeToCamelCase(string $str, string $expectedResult): void
    {
        $generatedString = StringTools::snakeToCamelCase($str);
        $this->assertSame($expectedResult, $generatedString);
    }

    public static function snakeToCamelCaseProvider(): array
    {
        return [
            ['Hello_World', 'helloWorld'],
            ['hello_world', 'helloWorld'],
            ['Text_in_camel_case', 'textInCamelCase']
        ];
    }

    #[Test, DataProvider('snakeToPascalCaseProvider')]
    public function snakeToPascalCase(string $str, string $expectedResult): void
    {
        $generatedString = StringTools::snakeToPascalCase($str);
        $this->assertSame($expectedResult, $generatedString);
    }

    public static function snakeToPascalCaseProvider(): array
    {
        return [
            ['Hello_World', 'HelloWorld'],
            ['hello_world', 'HelloWorld'],
            ['Text_in_pascal_case', 'TextInPascalCase']
        ];
    }

    #[Test, DataProvider('snakeCaseProvider')]
    public function snakeCase(string $str, bool $upperCase, $expectedResult): void
    {
        $generatedString = StringTools::toSnakeCase($str, $upperCase);
        $this->assertSame($expectedResult, $generatedString);
    }

    public static function snakeCaseProvider(): array
    {
        return [
            ['Hello World', false, 'hello_world'],
            ['HelloWorld', false, 'hello_world'],
            ['Text in snake case', false, 'text_in_snake_case'],
            ['Hello World', true, 'HELLO_WORLD'],
            ['HelloWorld', true, 'HELLO_WORLD'],
            ['Text in snake case', true, 'TEXT_IN_SNAKE_CASE'],
            ['Hello_World', false, 'hello_world'],
            ['Text_in_snake_case', false, 'text_in_snake_case'],
            ['Hello_World', true, 'HELLO_WORLD'],
            ['Text_in_snake_case', true, 'TEXT_IN_SNAKE_CASE']
        ];
    }

    #[Test, DataProvider('sanitizeProvider')]
    public function sanitize(string $str, ?string $placeholder, string $expectedResult): void
    {
        if ($placeholder === NULL) {
            $generatedString = StringTools::sanitize($str);
        } else {
            $generatedString = StringTools::sanitize($str, $placeholder);
        }
        $this->assertSame($expectedResult, $generatedString);
    }

    public static function sanitizeProvider(): array
    {
        return [
            ['Some text with spaces', '-', 'some-text-with-spaces'],
            ['Some text with spaces', NULL, 'some-text-with-spaces'],
            ['Some text with spaces', '_', 'some_text_with_spaces'],
            ['Some text with  multiple  spaces', '-', 'some-text-with-multiple-spaces'],
            ['Some text with %$* characters', '-', 'some-text-with-characters'],
            ['Some text with 12345 digits', '_', 'some_text_with_12345_digits'],
            ['Some text with café', '-', 'some-text-with-cafe'],
            ['%*£', '-', '-'],
            ['', NULL, ''],
            ['____', '_', '_']
        ];
    }

    #[Test, DataProvider('removeAccentsProvider')]
    public function removeAccents(string $str, string $expectedResult): void
    {
        $generatedString = StringTools::removeAccents($str);
        $this->assertSame($expectedResult, $generatedString);
    }

    public static function removeAccentsProvider(): array
    {
        return [
            ['àéèùö', 'aeeuo'],
            ['hjidsdf', 'hjidsdf']
        ];
    }
}
