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
use Warp\Tests\Fixtures\Entity\Value\AuthorType;

class EntityStorageTest extends TestCase
{
    use DatabaseContextTrait;

    public function testStore()
    {
        $author = new Author();
        $author->setName(bin2hex(random_bytes(32)));
        $author->setType(new AuthorType(AuthorType::TYPE_EXTERNAL));

        $context = $this->getDatabaseContext();
        $storage = new EntityStorage($context, new MappingManager());
        $storage->store($author);

        $book = new Book();
        $book->setAuthor($author);
        $book->setName(bin2hex(random_bytes(32)));
        $book->setDescription(bin2hex(random_bytes(32)));
        $storage->store($book);

        $row = $context->table('author')
            ->where('name', $author->getName())
            ->limit(1)
            ->fetch();
        Assert::assertInstanceOf(
            ActiveRow::class,
            $row
        );

        $row = $context->table('book')
            ->where('name', $book->getName())
            ->limit(1)
            ->fetch();
        Assert::assertInstanceOf(
            ActiveRow::class,
            $row
        );
    }

}
