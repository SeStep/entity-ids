<?php declare(strict_types=1);

namespace SeStep\EntityIds;

/**
 * A CharSet value object containing enumeration of characters
 */
class CharSet
{
    /** @var string base64 character set */
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

    public function generate(int $length): string
    {
        if (class_exists('Nette\Utils\Random')) {
            return \Nette\Utils\Random::generate($length, $this->getChars());
        }

        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $this->valueToChar(random_int(0, $this->base - 1));
        }

        return $id;
    }

    public function contains(string $word): bool
    {
        $length = strlen($word);
        for ($i = 0; $i < $length; $i++) {
            if ($this->charToValue($word[$i]) === -1) {
                return false;
            }
        }

        return true;
    }
}
