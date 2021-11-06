<?php
declare(strict_types=1);

namespace Warp\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\EntityHydrator;
use Warp\MappingManager;
use Warp\Tests\Fixtures\Entity\Book;

class EntityHydratorTest extends TestCase
{
    use DatabaseContextTrait;
    public function testAnonymous(): void
    {
        $row = $this->getDatabaseContext()->table('book')->limit(1)->fetch();
        $hydrator = new EntityHydrator(new MappingManager());
        /** @var Book $book */
        $book = $hydrator->hydrate($row, Book::class);
        Assert::assertInstanceOf(Book::class, $book);
    }

}
