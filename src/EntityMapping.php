<?php
declare(strict_types=1);

namespace Warp;

use Warp\Mapping\Column;
use Warp\Mapping\Entity;
use Warp\Mapping\Id;
use Warp\Mapping\Table;

class EntityMapping
{
    public Entity $entity;
    public Table $table;
    public Id $id;
    public array $columns = [];
    public array $relations = [];
    public array $indexes = [];

    public function isEntity(): bool
    {
        return isset($this->entity);
    }

    public function isRelation(string $propertyName): bool
    {
        return isset($this->relations[$propertyName]);
    }

}