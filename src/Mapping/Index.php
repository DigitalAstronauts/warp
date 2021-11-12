<?php

declare(strict_types=1);

namespace Warp\Mapping;
#[\Attribute]
class Index
{
    public function __construct(
        public string|array $name,
        public array $options = []
    )
    {
    }
}