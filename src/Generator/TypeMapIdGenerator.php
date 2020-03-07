<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use SeStep\EntityIds\CharSet;
use SeStep\EntityIds\IdGenerator;
use SeStep\EntityIds\Type\CheckSum;

final class TypeMapIdGenerator implements IdGenerator
{

    /** @var int[] map with types as keys */
    private $typeToCheckSumMap = [];
    /** @var string[] map with ints as keys */
    private $checkSumToTypeMap = [];

    /** @var CharSet */
    private $charSet;
    /** @var CheckSum */
    private $checkSum;

    /** @var int */
    private $length;

    /**
     * @param CharSet $charSet
     * @param CheckSum $checkSum
     * @param string[] $typeMap map of types where keys are expected check sums
     * @param int $length
     */
    public function __construct(
        CharSet $charSet,
        CheckSum $checkSum,
        array $typeMap = [],
        int $length = 12
    ) {
        $this->charSet = $charSet;
        $this->length = $length;
        $this->checkSum = $checkSum;

        $typeLimit = $this->checkSum->getDistinctValues();

        foreach ($typeMap as $checkSum => $type) {
            if (!is_int($checkSum)) {
                throw new \InvalidArgumentException("CheckSum '$checkSum' is not an integer");
            }
            if (0 > $checkSum || $checkSum >= $typeLimit) {
                throw new \InvalidArgumentException("Checksum for type '$type' ($checkSum) is not within" .
                    " allowed range <0, {$typeLimit})");
            }
            $this->checkSumToTypeMap[$checkSum] = $type;
            $this->typeToCheckSumMap[$type] = $checkSum;
        }
    }

    public function hasType(string $type): bool
    {
        return array_key_exists($type, $this->typeToCheckSumMap);
    }


    /**
     * Creates new ID of given type
     *
     * @param string|null $type
     * @return string
     */
    public function generateId(string $type = null): string
    {
        if ($type && !array_key_exists($type, $this->typeToCheckSumMap)) {
            throw new \InvalidArgumentException("Type '$type' is not registered");
        }

        if ($type) {
            $id = $this->charSet->generate($this->length);
            $id = $this->checkSum->adjustValueToSum($id, $this->typeToCheckSumMap[$type]);
        } else {
            do {
                $id = $this->charSet->generate($this->length);
            } while ($this->getType($id) !== null);
        }

        return $id;
    }

    public function getType(string $id): ?string
    {
        $checkSum = $this->checkSum($id);
        return $this->checkSumToTypeMap[$checkSum] ?? null;
    }

    private function checkSum(string $id): int
    {
        if (strlen($id) != $this->length) {
            return -1;
        }

        return $this->checkSum->compute($id);
    }

    /**
     * @inheritDoc
     */
    public function getTypes(): array
    {
        return array_keys($this->typeToCheckSumMap);
    }
}
