<?php
declare(strict_types=1);

namespace Warp\Tests;

use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Context;
use Nette\Database\Structure;
use Nette\Database\Table\ActiveRow;
use PHPUnit\Framework\Assert;
use Warp\EntityStorage;
use PHPUnit\Framework\TestCase;
use Warp\MappingManager;
use Warp\Tests\Fixtures\ConnectionFactory;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;

class EntityStorageTest extends TestCase
{
    public function testStore()
    {
        $author = new Author();
        $author->name = bin2hex(random_bytes(32));

        $context = $this->getDatabaseContext();
        $storage = new EntityStorage($context, new MappingManager());
        $storage->store($author);

        $book = new Book();
        $book->author = $author;
        $book->name = bin2hex(random_bytes(32));
        $book->description = bin2hex(random_bytes(32));
        $storage->store($book);

        $row = $context->table('author')
            ->where('name', $author->name)
            ->limit(1)
            ->fetch();
        Assert::assertInstanceOf(
            ActiveRow::class,
            $row
        );

        $row = $context->table('book')
            ->where('name', $book->name)
            ->limit(1)
            ->fetch();
        Assert::assertInstanceOf(
            ActiveRow::class,
            $row
        );
    }

    private function getDatabaseContext(): Context
    {
        static $context = null;
        if(!isset($context)) {
            $connection = ConnectionFactory::create();
            $context = new Context(
                $connection,
                new Structure(
                    $connection,
                    new MemoryStorage()
                )
            );
        }
        return $context;
    }

}
