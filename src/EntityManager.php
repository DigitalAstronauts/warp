<?php
declare(strict_types=1);

namespace Warp;

use League\Event\EventDispatcher;
use Nette\Caching\Storage;
use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\Structure;
use Throwable;

class EntityManager
{
    private Explorer $explorer;
    private MappingManager $mappingManager;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        private Connection $connection,
        private Storage $storage,
        private array $config = []
    )
    {
        $this->explorer = new Explorer(
            $this->connection,
            new Structure(
                $this->connection,
                $this->storage
            )
        );
        $this->mappingManager = new MappingManager($this->storage, $this->config);
    }

    public function withEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getExplorer(): Explorer
    {
        return $this->explorer;
    }

    public function getRepository(string $entityClass): EntityRepository
    {
        /** @var EntityMapping $entityMapping */
        $entityMapping = $this->mappingManager->getMapping($entityClass);
        $repositoryClass = $entityMapping->entity->repositoryClass;
        return new $repositoryClass(
            $this->getExplorer(),
            $this->mappingManager,
            $entityClass
        );
    }

    public function store(object &$entity): void
    {
        $this->getStorage()->store($entity);
    }

    public function delete(object $entity): void
    {
        $this->getStorage()->delete($entity);
    }

    public function transaction(callable $function): void
    {
        $this->getExplorer()->beginTransaction();
        try {
            $function();
            $this->getExplorer()->commit();
        } catch (Throwable $exception) {
            $this->getExplorer()->rollBack();
            throw $exception;
        }
    }

    private function getStorage(): EntityStorage
    {
        static $entityStorage = null;
        if(!isset($entityStorage)) {
            $entityStorage = new EntityStorage(
                $this->explorer,
                $this->mappingManager,
                $this->eventDispatcher ?? null
            );
        }
        return $entityStorage;
    }

}
