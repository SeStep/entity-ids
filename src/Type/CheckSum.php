<?php declare(strict_types=1);

namespace SeStep\EntityIds\Type;

use SeStep\EntityIds\CharSet;

class CheckSum
{
    /** @var CharSet */
    private $charSet;
    /** @var array */
    private $distinctPositions;
    /** @var */
    private $positionBase = [];
    /** @var int */
    private $modulo;

    public function __construct(CharSet $charSet, array $distinctPositions = [])
    {
        if (empty($distinctPositions)) {
            $distinctPositions[] = 0;
        }

        $this->charSet = $charSet;
        $this->distinctPositions = $distinctPositions;

        $this->modulo = $this->calculateTypeCheckSumModulo($charSet, $distinctPositions, $this->positionBase);
    }

    public function getDistinctValues(): int
    {
        return $this->modulo;
    }

    public function compute(string $value): int
    {
        $length = strlen($value);

        $sum = 0;
        for ($i = 0; $i < $length; $i++) {
            $val = $this->charSet->charToValue($value[$i]);
            if ($val == -1) {
                return -1;
            }
            $sum += $val;
        }

        $base = 1;
        foreach ($this->distinctPositions as $position) {
            if ($base > 1) {
                $charValue = $this->charSet->charToValue($value[$position]);
                $sum -= $charValue;
                $sum += $charValue * $base;
            }

            $base *= $this->charSet->getBase();
        }

        return $sum % $this->modulo;
    }

    public function adjustValueToSum(string $value, int $checkSum): string
    {
        if ($checkSum >= $this->modulo) {
            throw new \InvalidArgumentException("CheckSum '$checkSum' can not be reached");
        }

        $target = $checkSum;

        // subtract constant checksum value of constant part of value
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            if (in_array($i, $this->distinctPositions)) {
                continue;
            }
            $target -= $this->charSet->charToValue($value[$i]);
        }

        while ($target < 0) {
            $target += $this->modulo;
        }

        foreach (array_reverse($this->distinctPositions, true) as $pi => $position) {
            $base = $this->positionBase[$pi];

            $newCharValue = (int)($target / $base);
            /*dump([
                "Target" => $target,
                "Pos[$pi]" => $position,
                "Base" => $base,
                "new char value " => $newCharValue
            ]);*/
            $newChar = $this->charSet->valueToChar($newCharValue);

            $value[$position] = $newChar;
            $target -= $newCharValue * $base;
        }


        return $value;
    }

    private static function calculateTypeCheckSumModulo(
        CharSet $set,
        array &$positions,
        array &$positionBases = null
    ): int {
        $base = $set->getBase();
        if (empty($positions)) {
            return $base;
        }

        $maxCheckSum = 1;
        $checkedPositions = [];
        foreach ($positions as $pi => $position) {
            if ($position < 0) {
                throw new \InvalidArgumentException("Position must be a non-negative integer, got: $position");
            }
            if (array_key_exists($position, $checkedPositions)) {
                throw new \InvalidArgumentException("Position $position is duplicated in ["
                    . implode(', ', $positions) . ']');
            }
            $checkedPositions[$position] = true;
            if (isset($positionBases)) {
                $positionBases[$pi] = $maxCheckSum;
            }

            $maxCheckSum *= $base;
        }

        return $maxCheckSum;
    }
}
