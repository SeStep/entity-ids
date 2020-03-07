<?php declare(strict_types=1);

namespace SeStep\EntityIds\Generator;

use Nette\InvalidStateException;
use SeStep\EntityIds\IdGenerator;

class LengthDiscriminatingComposedIdGenerator implements IdGenerator
{
    /** @var IdGenerator[] */
    private $generators = [];

    /** @var mixed[] type to generator name map */
    private $typeToGenerator;

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
            foreach ($generator->getTypes() as $type) {
                $currentGenerator = $this->typeToGenerator[$type] ?? null;
                if ($currentGenerator !== null) {
                    throw new InvalidStateException("Can not register type '$type' of generator $length - "
                        . "it is already contained in generator " . $currentGenerator);
                }
                $this->typeToGenerator[$type] = $length;
            }
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
        return isset($this->typeToGenerator[$type]);
    }

    /**
     * Creates new ID of given type
     *
     * @param string|null $type
     * @return string
     */
    public function generateId(string $type = null): string
    {
        $generatorName = $this->typeToGenerator[$type] ?? null;
        if ($generatorName !== null) {
            return $this->generators[$generatorName]->generateId($type);
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

    /**
     * @inheritDoc
     */
    public function getTypes(): array
    {
        return array_keys($this->typeToGenerator);
    }
}
