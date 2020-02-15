<?php declare(strict_types=1);

namespace SeStep\EntityIds;

class CharSet
{
    const DEFAULT_LIST = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';

    /** @var string */
    private $charList;
    /** @var int */
    private $base;

    public function __construct(string $charList = self::DEFAULT_LIST)
    {
        $this->charList = $charList;
        $this->base = strlen($charList);
    }

    public function getChars(): string
    {
        return $this->charList;
    }

    public function getBase(): int
    {
        return $this->base;
    }

    public function charToValue(string $char): int
    {
        $charVal = strpos($this->charList, $char);
        if ($charVal === false) {
            return -1;
        }

        return $charVal;
    }

    public function valueToChar(int $value): ?string
    {
        if ($value < 0) {
            $value += $this->base;
            if ($value < 0) {
                return null;
            }
        }

        if ($value >= $this->base) {
            return null;
        }

        return $this->charList[$value];
    }
}
