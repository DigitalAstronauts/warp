<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Mapping;
use Warp\Tests\Fixtures\Entity\Proxy\BookProxy;
use Warp\Tests\Fixtures\Entity\Repository\BookRepository;

#[Mapping\Entity(repositoryClass: BookRepository::class)]
#[Mapping\Table(name: "book")]
class Book
{
    #[Mapping\Id]
    private int $id = 0;
    #[Mapping\ManyToOne(inversedBy: "books")]
    #[Mapping\JoinColumn(name: "author_id", options: ['update' => 'CASCADE'])]
    private Author $author;
    #[Mapping\ManyToOne]
    #[Mapping\JoinColumn(name: "parent_id", referencedColumnName: "id")]
    private ?Book $parent = null;
    #[Mapping\Column(name: "name", type: "string")]
    private string $name = '';
    #[Mapping\Column(name: "description", type: "text", options: ["limit" => Mapping\Option\StringType::LONG_TEXT])]
    private string $description = '';

    public function getParent(): ?Book
    {
        return $this->parent;
    }

    public function setParent(?Book $parent): void
    {
        $this->parent = $parent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): void
    {
        $this->author = $author;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
