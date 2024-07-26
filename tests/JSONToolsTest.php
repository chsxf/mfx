<?php

use chsxf\MFX\JSONTools;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JSONToolsTest extends TestCase
{
    #[Test]
    public function filterAndEncode(): void
    {
        $srcData = ['b' => 'true', 'i' => '10', 'f' => '12.34', 's' => 'test string'];

        $nativeJsonData = json_encode($srcData, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        $expectedResult = '{"b":true,"i":10,"f":12.34,"s":"test string"}';
        $encodedValue = JSONTools::filterAndEncode($srcData);

        $this->assertNotSame($encodedValue, $nativeJsonData);
        $this->assertSame($expectedResult, $encodedValue);
    }
}
