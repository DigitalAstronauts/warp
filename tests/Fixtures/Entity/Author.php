<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Mapping;
use Warp\Tests\Fixtures\Entity\Repository\AuthorRepository;

#[Mapping\Entity(repositoryClass: AuthorRepository::class)]
#[Mapping\Table(name: "author")]
class Author
{
    #[Mapping\Id]
    public int $id = 0;
    #[Mapping\Column(name: "name", type: "string")]
    public string $name = '';
    #[Mapping\OneToMany(targetEntity: Author::class, mappedBy: "author")]
    /** @var Author[] */
    public iterable $books = [];
}