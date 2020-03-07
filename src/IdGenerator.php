<?php declare(strict_types=1);

namespace SeStep\EntityIds;

/**
 * Provides way to create ids for given types and check types of given ids
 */
interface IdGenerator
{

    /**
     * Tests whether generator provides IDs for given type
     *
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool;

    /**
     * Creates new ID of given type
     *
     * @param string|null $type
     * @return string
     */
    public function generateId(string $type = null): string;

    /**
     * If possible, retrieves the type of given id
     *
     * @param string $id
     * @return string|null
     */
    public function getType(string $id): ?string;

    /**
     * Retrieves types supported by the id generator
     *
     * @return string[]
     */
    public function getTypes(): array;
}
