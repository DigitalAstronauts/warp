<?php
declare(strict_types=1);

namespace Warp;

use Warp\Entity;
use ReflectionClass;

trait StorageTrait
{
    protected function storeEntityData(Entity &$entity, array $data)
    {
        $table = $this->context->table((new ReflectionClass($entity))->getShortName());
        $row = $table->get($entity->id);
        if($row) {
            $row->update($data);
            return;
        }
        $entityClass = get_class($entity);
        $entity = $this->hydrator->hydrate(
            new $entityClass(),
            $table->insert($data)
        );
    }
}
