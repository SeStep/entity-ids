<?php declare(strict_types=1);

namespace SeStep\EntityIds\Type;

use SeStep\EntityIds\CharSet;

class CheckSum
{
    /** @var CharSet */
    private $charSet;
    /** @var int[] */
    private $distinctionPositions;
    /** @var int[] */
    private $positionBase = [];
    /** @var int */
    private $modulo;

    /**
     * CheckSum constructor.
     *
     * @param CharSet $charSet
     * @param int[] $distinctionPositions
     */
    public function __construct(CharSet $charSet, array $distinctionPositions = [])
    {
        if (empty($distinctionPositions)) {
            $distinctionPositions[] = 0;
        }

        $this->charSet = $charSet;
        $this->distinctionPositions = $distinctionPositions;

        $this->modulo = $this->calculateTypeCheckSumModulo($charSet, $distinctionPositions, $this->positionBase);
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
        foreach ($this->distinctionPositions as $position) {
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
            if (in_array($i, $this->distinctionPositions)) {
                continue;
            }
            $target -= $this->charSet->charToValue($value[$i]);
        }

        while ($target < 0) {
            $target += $this->modulo;
        }

        foreach (array_reverse($this->distinctionPositions, true) as $pi => $position) {
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

    /**
     * Calculates the checksum modulo according to given distinction positions and possible characters
     *
     * This modulo corresponds to count of possible unique values resulting from the checksum. If $positionBases
     * is provided, the function fills it with weighted coefficients of geometrically increasing multiples of modulo.
     *
     * @param CharSet $set
     * @param int[] $distinctionPositions
     * @param int[]|null $positionBases
     * @return int
     */
    private static function calculateTypeCheckSumModulo(
        CharSet $set,
        array &$distinctionPositions,
        array &$positionBases = null
    ): int {
        $base = $set->getBase();
        if (empty($distinctionPositions)) {
            return $base;
        }

        $maxCheckSum = 1;
        $checkedPositions = [];
        foreach ($distinctionPositions as $pi => $position) {
            if ($position < 0) {
                throw new \InvalidArgumentException("Position must be a non-negative integer, got: $position");
            }
            if (array_key_exists($position, $checkedPositions)) {
                throw new \InvalidArgumentException("Position $position is duplicated in ["
                    . implode(', ', $distinctionPositions) . ']');
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
