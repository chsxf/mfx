<?php

use chsxf\MFX\ArrayTools;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArrayToolsTest extends TestCase
{
    #[Test]
    public function reverseArrays(): void
    {
        $expectedResult = [
            'column1' => [10, 15],
            'column2' => [20, 25]
        ];

        $srcArrays = [
            ['column1' => 10, 'column2' => 20],
            ['column1' => 15, 'column2' => 25]
        ];
        $dstArrays = ArrayTools::reverseArrays($srcArrays);
        $this->assertSame($expectedResult, $dstArrays);
    }

    #[Test]
    public function concatArrays(): void
    {
        $expectedResult = [1, 2, 3, 4, 5, 6, 7];

        $srcArgs = [[1, 2, 3], 4, [5, 6, 7]];
        $result = ArrayTools::concatArrays($srcArgs);
        $this->assertSame($expectedResult, $result);

        $result2 = ArrayTools::concatArrays([1, 2, 3], 4, [5, 6, 7]);
        $this->assertSame($expectedResult, $result2);
    }

    #[Test]
    public function shuffle(): void
    {
        $srcArray = range(1, 100);
        $shuffledArray = range(1, 100);
        ArrayTools::shuffle($shuffledArray);

        $this->assertNotSame($srcArray, $shuffledArray);

        for ($i = 1; $i <= 100; $i++) {
            $this->assertContains($i, $shuffledArray);
        }
    }

    #[Test]
    public function isParameterArray(): void
    {
        $rc = new ReflectionClass(get_class($this));
        $rm = $rc->getMethod('isParameterArrayReflectionMethod');
        $params = $rm->getParameters();

        $this->assertTrue(ArrayTools::isParameterArray($params[0]));
        $this->assertTrue(ArrayTools::isParameterArray($params[1]));
        $this->assertFalse(ArrayTools::isParameterArray($params[2]));
    }

    private static function isParameterArrayReflectionMethod(array $array, array|false $union, int $notArray)
    {
    }
}
