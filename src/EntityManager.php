<?php
declare(strict_types=1);

namespace Warp;

use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Structure;
use Nette\Database\Context;
use Throwable;
use Webmozart\Assert\Assert;

class EntityManager
{
    private Context $context;
    public function __construct(
        private Connection $connection
    )
    {
        $this->context = new Context(
            $this->connection,
            new Structure(
                $this->connection,
                new DevNullStorage()
            )
        );
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getRepository(string $entityClass): EntityRepository
    {
        $repositoryClass = $entityClass::getRepositoryClass();
        return new $repositoryClass($this->getContext());
    }

    public function store(Entity &$entity): void
    {
        $this->getStorage($entity)->store($entity);
    }

    public function delete(Entity $entity): void
    {
        $this->getStorage($entity)->delete($entity);
    }

    public function transaction(callable $function): void
    {
        $this->getContext()->beginTransaction();
        try {
            $function();
            $this->getContext()->commit();
        } catch (Throwable $exception) {
            $this->getContext()->rollBack();
            throw $exception;
        }
    }


    protected function getStorage(Entity $entity): EntityStorage
    {
        $storageClass = $entity::getStorageClass();
        return new $storageClass($this->getContext());
    }
}
