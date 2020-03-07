<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use PHPUnit\Framework\TestCase;
use SeStep\EntityIds\CharSet;
use SeStep\EntityIds\Type\CheckSum;

class LengthDiscriminatingComposedIdGeneratorTest extends TestCase
{
    private function createGenerator(): LengthDiscriminatingComposedIdGenerator
    {
        $appleGenerator = new SingleTypeIdGenerator('apple', new CharSet('ABC'), 3);
        $charSet = new CharSet('01234');
        $checkSum = new CheckSum($charSet);
        $furnitureGenerator = new TypeMapIdGenerator($charSet, $checkSum, [
            0 => 'table',
            1 => 'mirror',
        ], 4);

        return new LengthDiscriminatingComposedIdGenerator([$appleGenerator, $furnitureGenerator]);
    }

    public function testHasType()
    {
        $generator = $this->createGenerator();

        self::assertTrue($generator->hasType('apple'));
        self::assertTrue($generator->hasType('mirror'));
        self::assertTrue($generator->hasType('table'));
    }

    public function testGenerateId(): void
    {
        $generator = $this->createGenerator();

        self::assertRegExp('#^[ABC]{3}$#', $generator->generateId('apple'));
        self::assertRegExp('#^[01234]{4}$#', $generator->generateId('table'));
        self::assertRegExp('#^[01234]{4}$#', $generator->generateId('mirror'));
    }

    /**
     * @dataProvider getTypeData
     */
    public function testGetType(string $id, string $expectedType)
    {
        $generator = $this->createGenerator();

        self::assertEquals($expectedType, $generator->getType($id));
    }

    public function testGetTypes()
    {
        $generator = $this->createGenerator();

        self::assertEquals(['apple', 'table', 'mirror'], $generator->getTypes());
    }

    public function getTypeData()
    {
        return [
            ['AAA', 'apple'], ['CAB', 'apple'],

            ['0000', 'table'], ['2102', 'table'],
            ['1000', 'mirror'], ['4322', 'mirror'],
        ];
    }
}
