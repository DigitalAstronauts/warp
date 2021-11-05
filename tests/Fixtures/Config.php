<?php

declare(strict_types=1);

namespace Warp\Tests\Fixtures;

use Webmozart\Assert\Assert;

class Config
{
    private function __construct(
        private array $config
    )
    {
    }

    public static function get(string $name)
    {
        Assert::keyExists(self::instance()->config, $name);;
        return self::instance()->config[$name];
    }

    private static function instance(): self
    {
        static $instance = null;
        if (!isset($instance)) {
            $instance = new self(require __DIR__ . '/../config.php');
        }
        return $instance;
    }
}