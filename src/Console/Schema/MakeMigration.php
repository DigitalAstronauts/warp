<?php

declare(strict_types=1);

namespace Warp\Console\Schema;

use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Warp\Console\Schema\Service\UpdateSchemaService;
use Warp\MappingManager;

class MakeMigration extends AbstractCommand
{
    protected static $defaultName = 'schema:make-migration';
    protected static $defaultDescription = 'Generates migration according defined schema.';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $service = $this->getService();
        $entityDir = $this->getEntityDir();
        $entityPrefix = $this->getEntityPrefix();
        $migrationDirectory = $this->getMigrationDirectory();
        $migrationPath = $service->makeMigration(
            $entityDir,
            $entityPrefix,
            $migrationDirectory
        );
        if($migrationPath) {
            $output->writeln("Created migration file: {$migrationPath}.");
        } else {
            $output->writeln('There is no migration to create.');
        }

        return self::SUCCESS;
    }

    private function getService(): UpdateSchemaService
    {
        $mappingManager = new MappingManager(new MemoryStorage());
        $connection = $this->createConnection();
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

    private function createConnection(): Connection
    {
        $envs = $this->getConfig()['environments'];
        $env = $envs[$envs['default_environment']];
        $dsn = sprintf(
            '%s:host=%s;dbname=%s;port=%d;charset=%s',
            $env['adapter'],
            $env['host'],
            $env['name'],
            $env['port'],
            $env['charset']
        );
        return new Connection($dsn, $env['user'], $env['pass']);
    }

    private function getEntityDir(): string
    {
        return $this->getConfig()['paths']['entitities'];
    }

    private function getEntityPrefix(): string
    {
        return $this->getConfig()['entityPrefix'];
    }

    private function getMigrationDirectory(): string
    {
        $array_key_first = array_key_first($this->getConfig()['paths']['migrations']);
        return $this->getConfig()['paths']['migrations'][$array_key_first];
    }
}
