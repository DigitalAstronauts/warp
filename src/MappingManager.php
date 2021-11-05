<?php
declare(strict_types=1);

namespace Warp;

use Nette\Caching\IStorage;
use Nette\Caching\Storages\MemoryStorage;
use Warp\Mapping\Column;
use Warp\Mapping\Entity;
use Warp\Mapping\Id;
use Warp\Mapping\JoinColumn;
use Warp\Mapping\ManyToOne;
use Warp\Mapping\OneToMany;
use Warp\Mapping\Table;

class MappingManager
{

    public function __construct(
        private ?IStorage $storage = null
    )
    {
        if(!isset($this->storage)) {
            $this->storage = new MemoryStorage();
        }
    }

    public function getMapping(object|string $entity)
    {
        $mapping = $this->storage->read(
            $this->getMappingKey($entity)
        );
        if(is_null($mapping)) {
            $mapping = $this->createMapping($entity);
            $this->storage->write(
                is_string($entity)
                    ? $entity
                    : get_class($entity),
                $mapping,
                []
            );
        }
        return $mapping;
    }

    private function getMappingKey(object|string $entity): string
    {
        return (new \ReflectionClass($entity))->getName();
    }

    private function createMapping(object|string $entity): EntityMapping
    {
        $rc = new \ReflectionClass($entity);
        $mapping = new EntityMapping();
        foreach ($rc->getAttributes() as $attribute) {
            $mappingAttribute = $attribute->newInstance();
            switch ($mappingAttribute::class) {
                case Entity::class:
                    $mapping->entity = $mappingAttribute;
                    break;
                case Table::class:
                    $mapping->table = $mappingAttribute;
                    break;
            }
        }
        foreach ($rc->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $mappingAttribute = $attribute->newInstance();
                switch ($mappingAttribute::class) {
                    case Id::class:
                        $mappingAttribute->propertyName = $property->getName();
                        $mapping->id = $mappingAttribute;
                        break;
                    case Column::class:
                        $mapping->columns[$property->getName()] = $mappingAttribute;
                        break;
                    case JoinColumn::class:
                        /** @var JoinColumn $mappingAttribute */
                        /** @var EntityMapping $joinColumnMapping */
                        $joinColumnMapping = $this->getMapping($property->getType()->getName());
                        $mappingAttribute->referencedTableName = $joinColumnMapping->table->name;
                        $mapping->columns[$property->getName()] = $mappingAttribute;
                        break;
                    case OneToMany::class:
                    case ManyToOne::class:
                        $mapping->relations[$property->getName()] = $mappingAttribute;
                }
            }
        }
        return $mapping;
    }
}