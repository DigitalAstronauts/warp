<?php
declare(strict_types=1);

namespace Warp;

use Warp\Entity;
use Warp\Hydrator;
use Warp\MapperInterface;
use Nette\Database\Table\IRow;

abstract class Mapper implements MapperInterface
{

    protected Hydrator $hydrator;

    final public function useHydrator(Hydrator $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    final public function map(Entity $entity, iterable $data): Entity
    {
        $callbacks = $this->getCallbacks($data);
        if ($callbacks) {
            $lazyPropertiesReflection = new \ReflectionProperty($entity, 'lazyProperties');
            $lazyPropertiesReflection->setAccessible(true);
            $lazyPropertiesReflection->setValue($entity, $callbacks);
        }
        foreach ($data as $property => $value) {
            if (!isset($callbacks[$property]) && $entity->__isset($property)) {
                $entity->$property = $value;
            }
        }
        return $entity;
    }

    protected function mapData(IRow $row)
    {
        return $row;
    }

    protected function getCallbacks(IRow $row): array
    {
        return [];
    }

}
