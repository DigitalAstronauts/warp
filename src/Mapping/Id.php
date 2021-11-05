<?php
declare(strict_types=1);

namespace Warp\Mapping;

#[\Attribute]
class Id extends Column
{
    public string $propertyName;
    public function __construct(
        string $name = 'id'
    )
    {
        parent::__construct(
            $name,
            'integer',
            [
                'signed' => false
            ]
        );
    }
}