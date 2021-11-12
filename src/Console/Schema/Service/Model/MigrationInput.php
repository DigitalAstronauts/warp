<?php

declare(strict_types=1);

namespace Warp\Console\Schema\Service\Model;

use Warp\Mapping\Column;
use Warp\Mapping\Id;
use Warp\Mapping\Index;
use Warp\Mapping\JoinColumn;
use Warp\Mapping\Table;

class MigrationInput
{

    public bool $create = false;
    public Id $id;
    public Table $table;
    /**
     * @var array
     */
    private array $columns = [
        'create' => [],
        'update' => [],
    ];
    /** @var array | Index[] */
    private array $indexes = [];
    private array $foreignKeys = [];

    public function addNewColumn(Column $column): void
    {
        $this->columns['create'][] = $column;
    }

    public function addColumnUpdate(Column $column): void
    {
        $this->columns['update'][] = $column;
    }

    /**
     * @return array | Column[]
     */
    public function getNewColumns(): array
    {
        return $this->columns['create'];
    }

    /**
     * @return array | Column[]
     */
    public function getUpdateColumns(): array
    {
        return $this->columns['update'];
    }

    /**
     * @return array | Index[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function addIndex(Index $index): void
    {
        $this->indexes[] = $index;
    }

    public function addForeignKey(\Warp\Mapping\JoinColumn $column)
    {
        $this->foreignKeys[] = $column;
    }

    /**
     * @return array | JoinColumn[]
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }
}