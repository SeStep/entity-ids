<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use SeStep\EntityIds\IdGenerator;

class LengthDiscriminatingComposedIdGenerator implements IdGenerator
{
    /** @var IdGenerator[] */
    private $generators = [];

    /**
     * ComposedIdGenerator constructor.
     *
     * @param IdGenerator[] $generators
     */
    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            $length = strlen($generator->generateId());
            if (isset($this->generators[$length])) {
                throw new \InvalidArgumentException("Multiple generators resulting in length of $length");
            }

            $this->generators[$length] = $generator;
        }
    }

    /**
     * Tests whether generator provides IDs for given type
     *
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->hasType($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates new ID of given type
     *
     * @param string|null $type
     * @return string
     */
    public function generateId(string $type = null): string
    {
        foreach ($this->generators as $generator) {
            if ($generator->hasType($type)) {
                return $generator->generateId($type);
            }
        }

        throw new \UnexpectedValueException("No generator available to generate id for type '$type'");
    }


    /**
     * If possible, retrieves the type of given id
     *
     * @param string $id
     * @return string|null
     */
    public function getType(string $id): ?string
    {
        $length = strlen($id);
        if (!isset($this->generators[$length])) {
            return null;
        }


        return $this->generators[$length]->getType($id);
    }
}
