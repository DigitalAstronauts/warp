<?php
declare(strict_types=1);

namespace Warp\Tests;


use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Context;
use Nette\Database\Structure;
use Warp\Tests\Fixtures\ConnectionFactory;

trait DatabaseContextTrait
{
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