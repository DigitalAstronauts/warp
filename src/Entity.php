<?php
declare(strict_types=1);

namespace Warp;

use Nette\SmartObject;

/**
 * @property int $id
 */
abstract class Entity
{
    use SmartObject {
        SmartObject::__get as smartGet;
        SmartObject::__set as smartSet;
    }

    protected array $lazyProperties = [];

    protected int $id = 0;
    protected function getId(): int
    {
        return $this->id;
    }

    protected function setId(int $id): void
    {
        $this->id = $id;
    }

    public function __get(string $name)
    {
        if(isset($this->lazyProperties[$name])) {
            $this->$name = $this->lazyProperties[$name]();
            unset($this->lazyProperties[$name]);
        }
        return $this->smartGet($name);
    }

    public function __set(string $name, $value)
    {
        $this->smartSet($name, $value);
        if(isset($this->lazyProperties[$name])) {
            unset($this->lazyProperties[$name]);
        }
    }

    protected static string $primaryKey = 'id';
    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    protected static string $tableName;
    public static function getTableName(): string
    {
        return static::$tableName;
    }

    protected static string $repositoryClass = EntityRepository::class;
    public static function getRepositoryClass(): string
    {
        return static::$repositoryClass;
    }

    protected static string $mapperClass;
    public static function getMapperClass(): string
    {
        return static::$mapperClass;
    }

    protected static string $storageClass = Storage::class;
    public static function getStorageClass(): string
    {
        return static::$storageClass;
    }
}
