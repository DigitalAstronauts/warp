<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Table\ActiveRow;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Warp\Mapping\JoinColumn;
use Warp\Mapping\OneToMany;

class EntityHydrator
{
    private ProxyBuilder $proxyBuilder;

    public function __construct(
        private MappingManager $mappingManager
    )
    {
        $this->proxyBuilder = new ProxyBuilder(
            $this->mappingManager,
            $this->mappingManager->getStorage()
        );
    }

    public function hydrate(
        ActiveRow $row,
        string    $entityClass
    )
    {
        $entityMapping = $this->mappingManager->getMapping($entityClass);
        $entity = $entityMapping->entity->proxyClass
            ? new $entityMapping->entity->proxyClass
            : $this->proxyBuilder->build($entityClass);

        $this->setEntityProperty(
            $entity,
            $entityMapping->id->propertyName,
            $row[$entityMapping->id->name]
        );
        foreach ($entityMapping->columns as $propertyName => $column) {
            if ($entityMapping->isRelation($propertyName)) {
                /** @var JoinColumn $column */
                $entity->__lazyProperties__[$propertyName] = function () use ($entity, $propertyName, $row, $column) {
                    $this->setEntityProperty(
                        $entity,
                        $propertyName,
                        $this->hydrate(
                            $row->ref($column->referencedTableName, $column->name),
                            $column->propertyType
                        )
                    );
                    unset($entity->__lazyProperties__[$propertyName]);
                };
            } else if (is_a($column->propertyType, ValueInterface::class, true)) {
                $value = new $column->propertyType($row[$column->name]);
                $this->setEntityProperty($entity, $propertyName, $value);
            } else {
                $this->setEntityProperty($entity, $propertyName, $row[$column->name]);
            }
        }
        foreach ($entityMapping->relations as $propertyName => $relation) {
            if ($relation instanceof OneToMany) {
                $relationMapping = $this->mappingManager->getMapping($relation->targetEntity);
                $this->setEntityProperty(
                    $entity,
                    $propertyName,
                    new EntityIterator(
                        $row->related($relationMapping->table->name),
                        fn(ActiveRow $row) => $this->hydrate(
                            $row,
                            $relation->targetEntity
                        )
                    )
                );
            }
        }
        return $entity;
    }

    private function setEntityProperty(
        object &$entity,
        string $propertyName,
               $value
    )
    {
        $accessor = new PropertyAccessor();
        $accessor->setValue($entity, $propertyName, $value);
    }
}
