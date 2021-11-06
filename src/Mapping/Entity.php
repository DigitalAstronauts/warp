<?php
declare(strict_types=1);

namespace Warp\Mapping;

use Warp\EntityRepository;

#[\Attribute]
class Entity
{
    public function __construct(
        public string $proxyClass,
        public string $repositoryClass = EntityRepository::class,
    )
    {
    }
}