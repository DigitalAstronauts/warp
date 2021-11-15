<?php

declare(strict_types=1);

namespace Warp\Tests;

use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Table\ActiveRow;
use PHPUnit\Framework\Assert;
use Warp\EntityManager;
use PHPUnit\Framework\TestCase;
use Warp\Tests\Fixtures\ConnectionFactory;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;
use Warp\Tests\Fixtures\Entity\Repository\BookRepository;
use Warp\Tests\Fixtures\Entity\Value\AuthorType;

class EntityManagerTest extends TestCase
{
    public function testGetRepository()
    {
        $entityManager = $this->getEntityManager();
        $bookRepository = $entityManager->getRepository(Book::class);
        Assert::assertInstanceOf(
            BookRepository::class,
            $bookRepository
        );
    }

    public function testStore(): void
    {
        $em = $this->getEntityManager();
        $name = bin2hex(random_bytes(16));

        $author = $this->getAuthorWithName($name, $em);
        // assert
        $explorer = $em->getExplorer();
        $row = $explorer->table('author')->where('name', $name)
            ->limit(1)
            ->fetch();
        Assert::assertInstanceOf(ActiveRow::class, $row);
        Assert::assertEquals($row['id'], $author->getId());
    }

    public function testDelete(): void
    {
        $em = $this->getEntityManager();
        $author = $this->getAuthorWithName(bin2hex(random_bytes(24)));
        // assert
        $explorer = $em->getExplorer();
        $em->delete($author);
        $row = $explorer->table('author')->where('name', $author->getName())
            ->limit(1)
            ->fetch();
        Assert::assertNull($row);
    }


    /**
     * @param string $name
     * @param EntityManager $em
     * @return Author
     */
    private function getAuthorWithName(string $name): Author
    {
        $em = $this->getEntityManager();
        $author = new Author();
        $author->setName($name);
        $author->setType(new AuthorType(AuthorType::TYPE_INTERNAL));
        $em->store($author);
        return $author;
    }

    private function getEntityManager(): EntityManager
    {
        $connection = ConnectionFactory::create();
        $storage = new DevNullStorage();
        return new EntityManager($connection, $storage);
    }

}
