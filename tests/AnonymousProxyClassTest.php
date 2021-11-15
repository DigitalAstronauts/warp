<?php

declare(strict_types=1);

namespace Warp\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\MappingManager;
use Warp\ProxyFactory;
use Warp\Tests\Fixtures\Entity\Book;

class AnonymousProxyClassTest extends TestCase
{
    public function testCreateProxy()
    {
        $entityClass = Book::class;
        $factory = new ProxyFactory($this->getMappingManager());
        $class = $factory->create($entityClass);
        Assert::assertIsArray($class);
        Assert::assertArrayHasKey('proxyClassName', $class);
        Assert::assertArrayHasKey('phpClassContent', $class);
    }

    public function getMappingManager(): MappingManager
    {
        return new MappingManager();
    }
}
