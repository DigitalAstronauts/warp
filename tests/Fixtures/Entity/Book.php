<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Entity;
use Warp\Tests\Fixtures\Entity\Repository\BookRepository;

class Book extends Entity
{
    protected static string $repositoryClass = BookRepository::class;

    public Author $author;
    public string $name = '';
    public string $description = '';
}
