<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Context;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Storage
{
    use StorageTrait;

    protected Context $context;

    public function __construct(
        Context $context,
    )
    {
        $this->context = $context;
    }

    public function store(Entity &$entity): void
    {
        $data = [];
        $columns = $this->context->getStructure()->getColumns($entity::getTableName());
        $accessor = new PropertyAccessor();
        foreach ($columns as $column) {
            if($accessor->isReadable($entity, $column['name'])) {
                $data[$column['name']] = $accessor->getValue($entity, $column['name']);
            }
        }
        if($primaryValue = $data[$entity::getPrimaryKey()]) {
           $this->context->table($entity::getTableName())
               ->where($entity::getPrimaryKey(), $primaryValue)
               ->update($data);
        } else {
            unset($data[$entity::getPrimaryKey()]);
            $row = $this->context->table($entity::getTableName())
                ->insert($data);
            $accessor->setValue($entity, $entity::getPrimaryKey(), $row[$entity::getPrimaryKey()]);
        }
    }



    public function delete(Entity $entity): void
    {
        $row = $this->context->table((new \ReflectionClass($entity))->getShortName())->get($entity->id);
        if ($row) {
            $row->delete();
        }
    }
}
