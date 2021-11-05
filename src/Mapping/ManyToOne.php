<?php
declare(strict_types=1);

namespace Warp\Mapping;
#[\Attribute]
class ManyToOne
{
    public function __construct(
        public string $inversedBy
    )
    {
    }
}