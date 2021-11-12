<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Mapping;
use Warp\Tests\Fixtures\Entity\Proxy\AuthorProxy;
use Warp\Tests\Fixtures\Entity\Repository\AuthorRepository;
use Warp\Tests\Fixtures\Entity\Value\AuthorType;

#[Mapping\Entity(repositoryClass: AuthorRepository::class, proxyClass: AuthorProxy::class)]
#[Mapping\Table(name: "author")]
class Author
{
    #[Mapping\Id]
    private int $id = 0;
    #[Mapping\Column(name: "name", type: "string")]
    private string $name = '';
    #[Mapping\Column(name: "type", type: "string", options: ['limit' => 30])]
    #[Mapping\Index(name: "type")]
    private AuthorType $type;
    /** @var Author[] */
    #[Mapping\OneToMany(targetEntity: Book::class, mappedBy: "author")]
    private iterable $books = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): AuthorType
    {
        return $this->type;
    }

    public function setType(AuthorType $type): void
    {
        $this->type = $type;
    }

    public function getBooks(): iterable
    {
        return $this->books;
    }

    public function setBooks(iterable $books): void
    {
        $this->books = $books;
    }
}