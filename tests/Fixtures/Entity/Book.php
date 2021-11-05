<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity;

use Warp\Mapping;
use Warp\Tests\Fixtures\Entity\Repository\BookRepository;

#[Mapping\Entity(repositoryClass: BookRepository::class)]
#[Mapping\Table(name: "book")]
class Book
{
    #[Mapping\Id]
    public int $id = 0;
    #[Mapping\ManyToOne(inversedBy: "books")]
    #[Mapping\JoinColumn(name: "author_id")]
    public Author $author;
    #[Mapping\Column(name: "name", type: "string")]
    public string $name = '';
    #[Mapping\Column(name: "description", type: "text", options: ["limit" => Mapping\Option\StringType::LONG_TEXT])]
    public string $description = '';
}
