<?php
declare(strict_types=1);

namespace Warp\Event;

use League\Event\HasEventName;

class Event implements HasEventName
{
    public function __construct(
        private string $name,
        private object $entity
    )
    {
    }

    public function eventName(): string
    {
        return $this->name;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public static function delete(string $class): string
    {
        return $class . '.delete';
    }

    public static function insert(string $class): string
    {
        return $class . '.insert';
    }

    public static function update(string $class): string
    {
        return $class . '.update';
    }

    public static function store(string $class): string
    {
        return $class . '.store';
    }
}