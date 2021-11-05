<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Entity;

/**
 * @property iterable|Book[] $books
 */
class Author extends Entity
{
    protected static string $tableName = 'author';

    public string $name = '';
    /** @var iterable|Book[] */
    protected iterable $books;

    protected function getBooks(): iterable
    {
        return $this->books;
    }
}