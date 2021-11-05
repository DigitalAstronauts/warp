<?php
declare(strict_types=1);

namespace Warp\Mapping;

#[\Attribute]
class JoinColumn extends Column
{
    public string $referencedTableName;
    public function __construct(
        public string $name,
        public string $referencedColumnName = 'id',
    )
    {
    }
}