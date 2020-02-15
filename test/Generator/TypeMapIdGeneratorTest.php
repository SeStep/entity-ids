<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use PHPUnit\Framework\TestCase;
use SeStep\EntityIds\CharSet;
use SeStep\EntityIds\Type\CheckSum;

class TypeMapIdGeneratorTest extends TestCase
{
    /**
     *
     *
     * @param string $expectedExceptionMessage
     * @param int $length
     * @param string[] $typeMap
     *
     * @dataProvider invalidArgumentsData
     */
    public function testInvalidArguments(
        string $expectedExceptionMessage,
        int $length,
        array $typeMap = []
    ): void {
        $this->expectExceptionMessage($expectedExceptionMessage);

        $charSet = new CharSet('ABCDabcd');
        new TypeMapIdGenerator($charSet, new CheckSum($charSet), $typeMap, $length);
    }

    /**
     * @return mixed[]
     */
    public function invalidArgumentsData(): array
    {
        return [
            'invalid check sum type' => [
                "CheckSum 'hello' is not an integer",
                6,
                [
                    'hello' => 'world',
                ],
            ],
            'invalid check sum value < 0' => [
                "Checksum for type 'apple' (-1) is not within allowed range <0, 8)",
                6,
                [
                    -1 => 'apple',
                ],
            ],
            'invalid check sum value > $maxChecksum' => [
                "Checksum for type 'apple' (8) is not within allowed range <0, 8)",
                6,
                [
                    8 => 'apple',
                ],
            ],
        ];
    }

    public function testGetId(): void
    {
        $charSet = new CharSet('ABC');
        $generator = new TypeMapIdGenerator($charSet, new CheckSum($charSet), [], 6);

        $this->assertRegExp('/[ABC]{6}/', $generator->generateId());
    }

    public function testGetIdCheckType(): void
    {
        $types = [
            2 => 'potion',
            7 => 'key',
            11 => 'door',
        ];

        $charSet = new CharSet('0123');
        $generator = new TypeMapIdGenerator($charSet, new CheckSum($charSet, [1, 3, 7]), $types, 10);

        $ids = array_map(function ($type) use ($generator) {
            return $generator->generateId($type);
        }, $types);

        foreach ($ids as $id) {
            $this->assertRegExp('/[0123]{10}/', $id);
        }

        $recognizedTypes = array_map(function ($id) use ($generator) {
            return $generator->getType($id);
        }, $ids);

        $this->assertEquals($types, $recognizedTypes);
    }
}
