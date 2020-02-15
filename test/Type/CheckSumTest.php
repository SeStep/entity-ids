<?php declare(strict_types=1);


namespace SeStep\EntityIds;

use PHPUnit\Framework\TestCase;
use SeStep\EntityIds\Type\CheckSum;

class CheckSumTest extends TestCase
{
    private static $hexDecCharSet;

    public static function setUpBeforeClass(): void
    {
        self::$hexDecCharSet = new CharSet('0123456789ABCDEF');
    }

    /**
     * @param $expectedValues
     * @param $positions
     *
     * @dataProvider distinctValuesData
     */
    public function testGetDistinctValues(int $expectedValues, array $positions)
    {
        $checkSum = new CheckSum(self::$hexDecCharSet, $positions);

        $this->assertEquals($expectedValues, $checkSum->getDistinctValues());
    }

    public function distinctValuesData()
    {
        return [
            [16, []],
            [16, [1]],
            [256, [0, 1]],
        ];
    }

    /**
     * @param string $value
     * @param int $expectedCheckSum
     * @param array $distinctPositions
     *
     * @dataProvider hexDecCheckSumData
     */
    public function testCheckSumBaseHexDec(
        string $value,
        int $expectedCheckSum,
        array $distinctPositions = []
    ) {
        $checkSum = new CheckSum(self::$hexDecCharSet, $distinctPositions);

        $this->assertEquals($expectedCheckSum, $checkSum->compute($value));
    }

    public function hexDecCheckSumData()
    {
        return [
            'basic 0' => ['0', 0],
            'basic 1' => ['1', 1],
            'basic F' => ['F', 15],
            'basic 10' => ['10', 1],
            'basic F2' => ['F3', 2],
            '1d 10' => ['10', 1, [0]],
            '1d F2' => ['F3', 2, [0]],
            '2d 12' => ['12', 18, [1, 0]],
            '2d-alt 12' => ['12', 33, [0, 1]],
        ];
    }

    /**
     * @param $value
     * @param $expectedCheckSum
     * @param array $distinctPositions
     *
     * @dataProvider adjustValueToSumData
     */
    public function testAdjustValueToSum($value, $expectedCheckSum, array $distinctPositions = [])
    {
        $checkSum = new CheckSum(self::$hexDecCharSet, $distinctPositions);

        $adjustedValue = $checkSum->adjustValueToSum($value, $expectedCheckSum);

        $this->assertEquals(
            $expectedCheckSum,
            $checkSum->compute($adjustedValue),
            "created value ($adjustedValue) must have expected checksum"
        );

        $maxDistance = count($distinctPositions) ?: 1;
        $this->assertLessThanOrEqual(
            $maxDistance,
            levenshtein($value, $adjustedValue),
            "new value should not differ in more than $maxDistance characters"
        );
    }

    public function adjustValueToSumData()
    {
        return [
            ['123', 7],
            ['F34', 7],
            ['123', 2],
            ['1A3D', 111, [2, 0, 1]],
        ];
    }
}
