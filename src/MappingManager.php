<?php
declare(strict_types=1);

namespace Warp;

use Nette\Caching\IStorage;
use Nette\Caching\Storage;
use Nette\Caching\Storages\MemoryStorage;
use Warp\Mapping\Column;
use Warp\Mapping\Entity;
use Warp\Mapping\Id;
use Warp\Mapping\Index;
use Warp\Mapping\JoinColumn;
use Warp\Mapping\ManyToOne;
use Warp\Mapping\OneToMany;
use Warp\Mapping\Table;

class MappingManager
{

    public function __construct(
        private ?Storage $storage = null,
        private array $config = [],
    )
    {
        if(!isset($this->storage)) {
            $this->storage = new MemoryStorage();
        }
    }

    /**
     * @param object|string $entity
     * @return mixed|EntityMapping
     */
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
                        $mappingAttribute->propertyType = $property->getType()->getName();
                        $mapping->id = $mappingAttribute;
                        break;
                    case Column::class:
                        $mappingAttribute->propertyName = $property->getName();
                        $mappingAttribute->propertyType = $property->getType()->getName();
                        $mapping->columns[$property->getName()] = $mappingAttribute;
                        break;
                    case JoinColumn::class:
                        /** @var JoinColumn $mappingAttribute */
                        /** @var EntityMapping $joinColumnMapping */
                        $joinColumnMapping = $this->getMapping($property->getType()->getName());
                        $mappingAttribute->referencedTableName = $joinColumnMapping->table->name;
                        $mappingAttribute->propertyName = $property->getName();
                        $mappingAttribute->propertyType = $property->getType()->getName();
                        $mapping->columns[$property->getName()] = $mappingAttribute;
                        break;
                    case OneToMany::class:
                    case ManyToOne::class:
                        $mapping->relations[$property->getName()] = $mappingAttribute;
                        break;
                    case Index::class:
                        $mapping->indexes[] = $mappingAttribute;
                        break;

                }
            }
        }
        return $mapping;
    }

    public function getProxyClassBasePath(): string
    {
        return $this->config['proxyClassBasePath'] ?? sys_get_temp_dir();
    }

    public function getStorage(): ?Storage
    {
        return $this->storage;
    }
}