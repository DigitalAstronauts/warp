<?php

declare(strict_types=1);

namespace Warp\Tests;

use PHPUnit\Framework\Assert;
use Warp\MappingManager;
use Warp\ProxyBuilder;
use PHPUnit\Framework\TestCase;
use Warp\Tests\Fixtures\Entity\Book;

class ProxyBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $pb = new ProxyBuilder(new MappingManager(
            null,
            [
                'proxyClassBasePath' => __DIR__ . '/proxies'
            ]
        ));
        $bookProxy = $pb->build(Book::class);
        Assert::assertInstanceOf(Book::class, $bookProxy);
    }
}
