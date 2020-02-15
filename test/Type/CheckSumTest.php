<?php declare(strict_types=1);

namespace SeStep\EntityIds\Type;

use PHPUnit\Framework\TestCase;
use SeStep\EntityIds\CharSet;

class CheckSumTest extends TestCase
{
    /** @var CharSet */
    private static $hexDecCharSet;

    public static function setUpBeforeClass(): void
    {
        self::$hexDecCharSet = new CharSet('0123456789ABCDEF');
    }

    /**
     * @param int $expectedValues
     * @param int[] $positions
     *
     * @dataProvider distinctValuesData
     */
    public function testGetDistinctValues(int $expectedValues, array $positions): void
    {
        $checkSum = new CheckSum(self::$hexDecCharSet, $positions);

        $this->assertEquals($expectedValues, $checkSum->getDistinctValues());
    }

    /**
     * @return mixed[]
     */
    public function distinctValuesData(): array
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
     * @param int[] $distinctionPositions
     *
     * @dataProvider hexDecCheckSumData
     */
    public function testCheckSumBaseHexDec(
        string $value,
        int $expectedCheckSum,
        array $distinctionPositions = []
    ): void {
        $checkSum = new CheckSum(self::$hexDecCharSet, $distinctionPositions);

        $this->assertEquals($expectedCheckSum, $checkSum->compute($value));
    }

    /**
     * @return mixed[]
     */
    public function hexDecCheckSumData(): array
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
     * @param string $value
     * @param int $expectedCheckSum
     * @param int[] $distinctPositions
     *
     * @dataProvider adjustValueToSumData
     */
    public function testAdjustValueToSum($value, $expectedCheckSum, array $distinctPositions = []): void
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

    /**
     * @return mixed[]
     */
    public function adjustValueToSumData(): array
    {
        return [
            ['123', 7],
            ['F34', 7],
            ['123', 2],
            ['1A3D', 111, [2, 0, 1]],
        ];
    }
}
