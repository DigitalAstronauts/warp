<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Context;
use Nette\Database\Explorer;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Warp\Mapping\Column;

class EntityStorage
{
    public function __construct(
        private Explorer        $explorer,
        private MappingManager $mappingManager
    )
    {
    }

    public function store(object &$entity): void
    {
        $mapping = $this->mappingManager->getMapping($entity);
        $data = $this->extractDataFromEntity($mapping, $entity);

        $id = $this->getEntityValue($entity, $mapping->id->name);
        if($id) {
            $this->explorer->table($mapping->table->name)
                ->where($mapping->id->name, $id)
                ->update($data);
        } else {
            $row = $this->explorer->table($mapping->table->name)
                ->insert($data);
            $this->getAccessor()->setValue(
                $entity,
                $mapping->id->name,
                $this->getEntityValue($row, $mapping->id->propertyName)
            );
        }
    }

    public function delete($entity): void
    {
        $mapping = $this->mappingManager->getMapping($entity);
        $id = $this->getEntityValue($entity, $mapping->id->name);
        $this->explorer->table($mapping->table->name)
            ->where($mapping->id->name, $id)
            ->delete();
    }

    public function getEntityValue(object $entity, string $propertyName)
    {
        return $this->getAccessor()
            ->getValue($entity, $propertyName);
    }

    private function getAccessor(): PropertyAccessor
    {
        static $accessor = null;
        if (!isset($accessor)) {
            $accessor = new PropertyAccessor();
        }
        return $accessor;
    }

    private function extractDataFromEntity(EntityMapping $mapping, object $entity): array
    {
        $data = [];
        foreach ($mapping->columns as $propertyName => $column) {
            /** @var Column $column */
            $value = $this->getEntityValue($entity, $propertyName);
            if (!is_scalar($value)) {
                if (is_object($value) && $value instanceof AbstractValue) {
                    $value = $value->jsonSerialize();
                }
                if (is_object($value)) {
                    $valueMapping = $this->mappingManager->getMapping($value);
                    if ($valueMapping->isEntity()) {
                        $value = $this->getEntityValue($value, $valueMapping->id->propertyName);
                    }
                }
            }
            $data[$column->name] = $value;
        }
        return $data;
    }
}
