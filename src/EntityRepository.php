<?php
declare(strict_types=1);

namespace Warp;

use Warp\Entity;
use Warp\Hydrator;
use Warp\EntityIterator;
use Warp\Exception\EntityNotFoundException;
use Warp\RepositoryInterface;
use Iterator;
use LogicException;
use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;
use ReflectionClass;

class EntityRepository
{
    protected static string $entityClass;
    protected static string $tableName;
    protected Context $context;
    protected Hydrator $hydrator;

    public function all(): iterable
    {
        return $this->resultIterator(
            $this->getTableResult()
        );
    }

    public function find(array $where): iterable
    {
        return $this->resultIterator(
            $this->getTableResult()->where($where)
        );
    }

    public function findOne(array $where): Entity
    {
        $row = $this->getTableResult()
            ->where($where)
            ->limit(1)
            ->fetch();
        $this->validateRow($row);
        return $this->hydrator->hydrate(
            new static::$entityClass(),
            $row
        );
    }

    public function byId(int $id): Entity
    {
        $primaryKey = (static::$entityClass)::getPrimaryKey();
        $row = $this->getTableResult()
            ->where($primaryKey, $id)
            ->limit(1)
            ->fetch();
        $this->validateRow($row, sprintf(
            "Entity %s with `%s` key: %s was not found.",
            static::$entityClass,
            $primaryKey,
            $id
        ));
        return $this->hydrator->hydrate(
            new static::$entityClass(),
            $row
        );
    }

    protected function validateRow(IRow $row = null, string $message = null): void
    {
        if(!$row) {
            throw new EntityNotFoundException(
                isset($message)
                    ? $message
                    : sprintf("Entity %s was not found.", static::$entityClass)
            );
        }
    }

    protected function resultIterator(Selection $selection): Iterator
    {
        return new EntityIterator(
            $selection,
            function(IRow $row) {
                return $this->hydrator->hydrate(
                    new static::$entityClass(),
                    $row
                );
            }
        );
    }

    protected function getTableResult(): Selection
    {
        return $this->context->table($this->getTableName());
    }

    public function getTableName()
    {
        static $tableName = null;
        if(!isset(static::$entityClass)) {
            throw new LogicException(
                sprintf(
                    "You must specify a repository property: `%s::\$entityClass`.",
                    static::class
                )
            );
        }
        if(!isset($tableName)) {
            $tableName =  (new ReflectionClass(static::$entityClass))
                ->getShortName();
        }
        return $tableName;
    }

}
