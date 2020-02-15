<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use PHPUnit\Framework\TestCase;
use SeStep\EntityIds\CharSet;

class SingleTypeIdGeneratorTest extends TestCase
{
    public function testHasType()
    {
        $generator = new SingleTypeIdGenerator('apple', new CharSet('ABC'), 4);
        self::assertTrue($generator->hasType('apple'));
        self::assertFalse($generator->hasType('orange'));
    }

    public function testGenerateId()
    {
        $generator = new SingleTypeIdGenerator('apple', new CharSet('ABC'), 4);

        foreach (['apple', 'orange', null] as $type) {
            self::assertRegExp('/^[ABC]{4}$/', $generator->generateId($type),
                "Type '$type' should match id pattern");
        }
    }

    public function testGetType()
    {
        $generator = new SingleTypeIdGenerator('apple', new CharSet('ABC'), 4);

        foreach (['ABC', 'AAAAA'] as $badId) {
            self::assertNull($generator->getType($badId));
        }

        self::assertEquals('apple', $generator->getType('ABCA'));
    }
}
