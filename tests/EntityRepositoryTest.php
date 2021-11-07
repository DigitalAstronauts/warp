<?php
declare(strict_types=1);

namespace Warp\Tests;

use PHPUnit\Framework\Assert;
use Warp\EntityRepository;
use PHPUnit\Framework\TestCase;
use Warp\EntityStorage;
use Warp\MappingManager;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;
use Warp\Tests\Fixtures\Entity\Value\AuthorType;

class EntityRepositoryTest extends TestCase
{
    use DatabaseContextTrait;

    public function testFindOne()
    {
        $author = $this->makeTestAuthor();
        $repository = new EntityRepository(
            $this->getDatabaseContext(),
            new MappingManager(),
            Author::class
        );
        $testAuthor = $repository->findOne($author->getId());
        Assert::assertEquals($author->getId(), $testAuthor->getId());
    }

    public function testAccessRelation(): void
    {
        $author = $this->makeTestAuthor();
        $book = new Book();
        $book->setName(bin2hex(random_bytes(32)));
        $book->setDescription(bin2hex(random_bytes(32)));
        $book->setAuthor($author);
        $this->getEntityStorage()->store($book);

        $repository = new EntityRepository(
            $this->getDatabaseContext(),
            new MappingManager(),
            Book::class
        );
        $testBook = $repository->findOne($book->getId());
        Assert::assertEquals($book->getId(), $testBook->getId());
    }

    private function makeTestAuthor(): Author
    {
        $storage = $this->getEntityStorage();
        $name = bin2hex(random_bytes(16));
        $author = new Author();
        $author->setName($name);
        $author->setType(new AuthorType(AuthorType::TYPE_INTERNAL));
        $storage->store($author);
        return $author;
    }

    private function getEntityStorage(): EntityStorage
    {
        $context = $this->getDatabaseContext();
        $storage = new EntityStorage($context, new MappingManager());
        return $storage;
    }

}
