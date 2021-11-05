<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Context;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Warp\Mapping\Column;

class EntityStorage
{
    public function __construct(
        private Context $context,
        private MappingManager $mappingManager
    )
    {
    }

    public function store(object &$entity): void
    {
        $data = [];
        $mapping = $this->mappingManager->getMapping($entity);
        foreach ($mapping->columns as $propertyName=>$column) {
            /** @var Column $column */
            $value = $this->getEntityValue($entity, $propertyName);
            if(!is_scalar($value)) {
                if(is_object($value) && $value instanceof ValueInterface) {
                    $value = $value->getValue();
                }
                if(is_object($value)) {
                    $valueMapping = $this->mappingManager->getMapping($value);
                    if($valueMapping->isEntity()) {
                        $value = $this->getEntityValue($value, $valueMapping->id->propertyName);
                    }
                }
            }
            $data[$column->name] = $value;
        }
        $id = $this->getEntityValue($entity, $mapping->id->name);
        if($id) {
            $this->context->table($mapping->table->name)
                ->where($mapping->id->name, $id)
                ->update($data);
        } else {
            $row = $this->context->table($mapping->table->name)
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
}
