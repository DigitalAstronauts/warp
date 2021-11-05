<?php

declare(strict_types=1);

namespace Warp\Tests\_Database;

use PHPUnit\Framework\Assert;
use Warp\_Database\Storage;
use Warp\EntityManager;
use PHPUnit\Framework\TestCase;
use Warp\RepositoryInterface;
use Warp\Tests\Fixtures\ConnectionFactory;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;
use Warp\Tests\Fixtures\Entity\Repository\BookRepository;

class EntityManagerTest extends TestCase
{
    public function testGetRepository(): void
    {
        $em = $this->getEntityManager();
        Assert::assertInstanceOf(
            BookRepository::class,
            $em->getRepository(Book::class)
        );
        Assert::assertInstanceOf(
            RepositoryInterface::class,
            $em->getRepository(Author::class)
        );
    }

    public function testStore()
    {
        $author = new Author();
        $name = bin2hex(random_bytes(32));
        $author->name = $name;
        $em = $this->getEntityManager();
        $em->store($author);
        $row = $em->getContext()->table('author')
            ->where('name', $name)
            ->limit(1)
            ->fetch();
        Assert::assertEquals($row['id'], $author->id);
    }


    /**
     * @return EntityManager
     */
    private function getEntityManager(): EntityManager
    {
        return new EntityManager(
            ConnectionFactory::create()
        );
    }


}
