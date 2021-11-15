<?php
declare(strict_types=1);

namespace Warp;

use Nette\Database\Explorer;

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
        return $this->getHydrator()->hydrate(
            $row,
            $this->entityClass
        );
    }

    public function findOne($id)
    {
        $row = $this->explorer->table($this->entityMapping->table->name)
            ->where($this->entityMapping->id->name, $id)
            ->limit(1)
            ->fetch();

        return $this->getHydrator()->hydrate(
            $row,
            $this->entityClass
        );
    }

    private function getHydrator()
    {
        if (!isset(self::$entityHydrator)) {
            self::$entityHydrator = new EntityHydrator($this->mappingManager);
        }
        return self::$entityHydrator;
    }

}
