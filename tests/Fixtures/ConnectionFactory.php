<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures;

use Nette\Database\Connection;

class ConnectionFactory
{
    public static function create(): Connection
    {
        return new Connection(
            Config::get('dsn'),
            Config::get('user'),
            Config::get('password'),
        );
    }
}
