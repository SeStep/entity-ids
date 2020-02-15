<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use SeStep\EntityIds\CharSet;
use SeStep\EntityIds\IdGenerator;

class SingleTypeIdGenerator implements IdGenerator
{
    /** @var string */
    private $type;
    /** @var CharSet */
    private $charSet;
    /** @var int */
    private $length;

    public function __construct(string $type, CharSet $charSet, int $length)
    {
        $this->type = $type;
        $this->charSet = $charSet;
        $this->length = $length;
    }

    public function hasType(string $type): bool
    {
        return $this->type == $type;
    }

    /**
     * Creates new ID regardless of given type
     *
     * @param string|null $type
     * @return string
     */
    public function generateId(string $type = null): string
    {
        return $this->charSet->generate($this->length);
    }

    /**
     * If possible, retrieves the type of given id
     *
     * @param string $id
     * @return string|null
     */
    public function getType(string $id): ?string
    {
        $match = strlen($id) == $this->length && $this->charSet->contains($id);

        return $match ? $this->type : null;
    }
}
