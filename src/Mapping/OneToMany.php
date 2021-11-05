<?php
declare(strict_types=1);

namespace Warp\Mapping;
#[\Attribute]
class OneToMany
{
    public function __construct(
        public string $targetEntity,
        public string $mappedBy
    )
    {
    }
}