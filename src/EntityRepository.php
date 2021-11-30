<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class EntityRepository
{
    private EntityMapping $entityMapping;
    private static EntityHydrator $entityHydrator;

    public function __construct(
        private Explorer       $explorer,
        private MappingManager $mappingManager,
        private string         $entityClass
    )
    {
        $this->entityMapping = $this->mappingManager->getMapping($this->entityClass);
    }

    public function findOneBy(array $where)
    {
        $row = $this->explorer->table($this->entityMapping->table->name)
            ->where($where)
            ->limit(1)
            ->fetch();
        return $row
            ? $this->getHydrator()->hydrate(
                $row,
                $this->entityClass
            )
            : null;
    }

    public function findOne($id)
    {
        $row = $this->explorer->table($this->entityMapping->table->name)
            ->where($this->entityMapping->id->name, $id)
            ->limit(1)
            ->fetch();

        return $row
            ? $this->getHydrator()->hydrate(
                $row,
                $this->entityClass
            )
            : null;
    }

    public function find(array $where = [], string $sort = ''): EntityIterator
    {
        $selection = $this->explorer->table($this->entityMapping->table->name);
        if($where) $selection->where($where);
        if($sort) $selection->order($sort);
         return new EntityIterator(
             $selection,
             fn(ActiveRow $row) => $this->getHydrator()->hydrate(
                 $row,
                 $this->entityClass
             )
         );
    }

    public function all(): EntityIterator
    {
        return $this->find();
    }

    private function getHydrator()
    {
        if (!isset(self::$entityHydrator)) {
            self::$entityHydrator = new EntityHydrator($this->mappingManager);
        }
        return self::$entityHydrator;
    }

}
