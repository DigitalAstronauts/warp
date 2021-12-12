<?php
declare(strict_types=1);

namespace Warp\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\EntityHydrator;
use Warp\EntityRepository;
use Warp\EntityStorage;
use Warp\MappingManager;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;
use Warp\Tests\Fixtures\Entity\Value\AuthorType;

class EntityHydratorTest extends TestCase
{
    use DatabaseContextTrait;
    public function testHydrate(): void
    {
        $this->prepareRow();
        $row = $this->getDatabaseContext()->table('book')->limit(1)->fetch();
        $hydrator = new EntityHydrator(new MappingManager());
        /** @var Book $book */
        $book = $hydrator->hydrate($row, Book::class);
        Assert::assertInstanceOf(Book::class, $book);
    }

    public function testManyToOne(): void
    {
        // prepare
        $context = $this->getDatabaseContext();
        $mappingManager = new MappingManager();
        $storage = new EntityStorage($context, $mappingManager);

        $author = new Author();
        $author->setName('Martin Krizan');
        $author->setType(new AuthorType(AuthorType::TYPE_INTERNAL));
        $storage->store($author);

        for ($i = 0; $i<3; $i++) {
            $book = new Book();
            $book->setName(bin2hex(random_bytes(32)));
            $book->setDescription(bin2hex(random_bytes(32)));
            $book->setAuthor($author);
            $storage->store($book);
        }
        // execute
        $repo = new EntityRepository($context, $mappingManager, Author::class);
        /** @var Author $testAuthor */
        $testAuthor = $repo->findOne($author->getId());
        // assert
        Assert::assertNotEmpty($testAuthor->getBooks());
        foreach ($testAuthor->getBooks() as $authorBook) {
            Assert::assertInstanceOf(
                Book::class,
                $authorBook
            );
        }
    }

    private function prepareRow(): void
    {
        $author = $this->getDatabaseContext()->table('author')->insert([
            'name' => bin2hex(random_bytes(16)),
            'type' => mt_rand(0, 1) ? 'internal' : 'external',
        ]);
        $this->getDatabaseContext()->table('book')->insert([
            'author_id' => $author['id'],
            'parent_id' => null,
            'name' => bin2hex(random_bytes(16)),
            'description' => implode(
                ' ',
                array_map(
                    fn() => bin2hex(random_bytes(mt_rand(1, 8))),
                    range(20, mt_rand(20, 100))
                )
            ),
        ]);
    }

}
