<?php
declare(strict_types=1);

namespace Warp\Mapping;
#[\Attribute]
class Column
{
    public string $propertyName;
    public string $propertyType;
    public function __construct(
        public string $name,
        public string $type,
        public array $options = []
    )
    {
    }
}