<?php
declare(strict_types=1);

namespace Warp\Mapping;
#[\Attribute]
class Table
{
    public function __construct(
        public string $name
    )
    {
    }
}