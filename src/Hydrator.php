<?php
declare(strict_types=1);

namespace Warp;

use Warp\Exception\EntityNotFoundException;

class Hydrator
{

    /** @var MapperInterface[] */
    private array $mappers;
    private bool $useDefaultHydrator;

    public function __construct(array $mappers, bool $useDefaultHydrator = true)
    {
        $this->mappers = $mappers;
        $this->useDefaultHydrator = $useDefaultHydrator;
    }

    public function hydrate(Entity $entity, ?iterable $data): Entity
    {
        if(is_null($data)) {
            throw new EntityNotFoundException(
                (new \ReflectionClass($entity))->getShortName() . " was not found."
            );
        }
        $entityClass = get_class($entity);
        if(isset($this->mappers[$entityClass])) {
            /** @var MapperInterface $mapper */
            $mapper = $this->mappers[$entityClass];
            $mapper->useHydrator($this);
            return $mapper->map($entity, $data);
        }
        if($this->useDefaultHydrator) {
            foreach ($data as $property=>$value) {
                if($entity->__isset($property)) {
                    $entity->$property = $value;
                }
            }
            return $entity;
        }
        throw new \LogicException(
            "There is not defined mapper for entity: " . $entityClass
        );
    }
}
