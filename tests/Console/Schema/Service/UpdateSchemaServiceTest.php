<?php

declare(strict_types=1);

namespace Warp\Tests\Console\Schema\Service;

use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Context;
use Nette\Database\Structure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\Console\Schema\Service\UpdateSchemaService;
use Warp\MappingManager;
use Warp\Tests\Fixtures\ConnectionFactory;

class UpdateSchemaServiceTest extends TestCase
{
    public function testMakeMigration()
    {
        $service = $this->getService();
        $entityDir = __DIR__ . '/../../../Fixtures/Entity';
        $entityPrefix = 'Warp\Tests\Fixtures\Entity';
        $migrationDirectory = __DIR__ . '/../../../migrations';
        $path = $service->makeMigration(
            $entityDir,
            $entityPrefix,
            $migrationDirectory
        );
        Assert::assertTrue(
            file_exists($path)
        );
    }

    private function getService(): UpdateSchemaService
    {
        $mappingManager = new MappingManager(new MemoryStorage());
        $connection = ConnectionFactory::create();
        $context = new Context(
            $connection,
            new Structure($connection, new MemoryStorage())
        );

        $service = new UpdateSchemaService(
            $context,
            $mappingManager
        );
        return $service;
    }


}
