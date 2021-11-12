<?php
declare(strict_types=1);

namespace Warp\Mapping;

#[\Attribute]
class JoinColumn extends Column
{
    public string $referencedTableName;
    public array $options = ['signed' => false];
    public function __construct(
        public string $name,
        public string $referencedColumnName = 'id',
        array $options = ['signed' => false]
    )
    {
        $this->options = $options + $this->options;
    }
}