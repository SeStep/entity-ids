<?php declare(strict_types=1);

namespace SeStep\EntityIds;

use PHPUnit\Framework\TestCase;

class CharSetTest extends TestCase
{
    /**
     * @param string $char
     * @param int $expectedValue
     *
     * @dataProvider charToValueData
     */
    public function testCharToValue($char, $expectedValue): void
    {
        $charSet = new CharSet();

        $this->assertEquals($expectedValue, $charSet->charToValue($char));
    }

    public function testCharToValueInvalid(): void
    {
        $charSet = new CharSet('abc');

        $this->assertEquals(-1, $charSet->charToValue('d'));
    }

    /**
     * @param string $expectedChar
     * @param int $value
     *
     * @dataProvider charToValueData
     */
    public function testValueToChar($expectedChar, $value): void
    {
        $charSet = new CharSet();

        $this->assertEquals($expectedChar, $charSet->valueToChar($value));
    }

    public function testValueToCharSpecial(): void
    {
        $charSet = new CharSet('ABCD');
        $this->assertEquals('D', $charSet->valueToChar(-1));
        $this->assertEquals('C', $charSet->valueToChar(-2));
        $this->assertEquals('A', $charSet->valueToChar(-4));
    }

    /**
     * @return mixed[] tested dataset
     */
    public function charToValueData(): array
    {
        return [
            ['A', 0],
            ['E', 4],
            ['Z', 25],
            ['a', 26],
            ['h', 33],
            ['z', 51],
            ['0', 52],
            ['9', 61],
            ['_', 62],
            ['-', 63],
        ];
    }

    public function testValueToCharInvalid(): void
    {
        $charSet = new CharSet('abc');

        $this->assertNull($charSet->valueToChar(-5));
        $this->assertNull($charSet->valueToChar(4));
    }

    public function testContains(): void
    {
        $charSet = new CharSet('abc');

        $this->assertTrue($charSet->contains('a'));
        $this->assertTrue($charSet->contains('aabbcc'));

        $this->assertFalse($charSet->contains('123'));
    }
}
