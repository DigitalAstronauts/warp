<?php
declare(strict_types=1);

namespace Warp;

use League\Event\EventDispatcher;
use Nette\Database\Explorer;
use Proxima\Entity\Attribute\AttributeGroup;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Warp\Event\Event;
use Warp\Mapping\Column;

class EntityStorage
{
    public function __construct(
        private Explorer        $explorer,
        private MappingManager $mappingManager,
        private ?EventDispatcher $eventDispatcher = null
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
            $this->eventDispatcher?->dispatch(new Event(
                Event::update($this->getClassName($entity)),
                $entity
            ));
        } else {
            $row = $this->explorer->table($mapping->table->name)
                ->insert($data);
            $this->getAccessor()->setValue(
                $entity,
                $mapping->id->name,
                $this->getEntityValue($row, $mapping->id->propertyName)
            );
            $this->eventDispatcher?->dispatch(new Event(
                Event::insert($this->getClassName($entity)),
                $entity
            ));
        }
        $this->eventDispatcher?->dispatch(new Event(
            Event::store($this->getClassName($entity)),
            $entity
        ));
    }

    public function delete($entity): void
    {
        $mapping = $this->mappingManager->getMapping($entity);
        $id = $this->getEntityValue($entity, $mapping->id->name);
        $this->explorer->table($mapping->table->name)
            ->where($mapping->id->name, $id)
            ->delete();
        $this->eventDispatcher?->dispatch(new Event(
            Event::delete($this->getClassName($entity)),
            $entity
        ));
    }

    private function getClassName(object $entity): string
    {
        $class = get_class();
        if(method_exists($entity, '__isProxyClass') && $entity->__isProxyClass()) {
            $class = get_parent_class($class);
        }
        return $class;
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
