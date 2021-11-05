<?php

declare(strict_types=1);

namespace Warp\Tests;

use DatabaseConnection;
use Nette\Database\Connection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\Tests\Fixtures\ConnectionFactory;

class TestConnection extends TestCase
{
    public function testFactory(): void
    {
        $connection = ConnectionFactory::create();
        Assert::assertInstanceOf(Connection::class, $connection);
    }
}
