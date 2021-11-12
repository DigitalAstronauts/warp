<?php

declare(strict_types=1);

namespace Warp\Console\Schema\Service;

use Nette\Database\Context;
use Nette\Database\DriverException;
use Nette\InvalidArgumentException;
use Nette\PhpGenerator\ClassType;
use Phinx\Migration\AbstractMigration;
use Warp\Console\Schema\Service\Model\MigrationInput;
use Warp\EntityMapping;
use Warp\Mapping\Column;
use Warp\Mapping\Index;
use Warp\Mapping\JoinColumn;
use Warp\MappingManager;

class UpdateSchemaService
{
    public function __construct(
        private Context        $context,
        private MappingManager $mappingManager
    )
    {
    }

    /**
     * @param string $entityDir
     * @param string $entityPrefix
     * @return iterable|MigrationInput[]
     */
    public function getMigrationInputIterator(string $entityDir, string $entityPrefix): iterable
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($entityDir)
        );
        foreach ($iterator as $info) {
            /** @var $info \SplFileInfo */
            if (is_dir($info->getFilename())) continue;
            $basename = str_replace('.' . $info->getExtension(), '', $info->getBasename());
            $className = sprintf('%s\\%s', $entityPrefix, $basename);
            if (class_exists($className)) {
                $migrationInput = new MigrationInput();
                /** @var EntityMapping $mapping */
                $mapping = $this->mappingManager->getMapping($className);
                if (!isset($mapping->entity) || $mapping->entity->proxyClass == $className) continue;
                $table = $migrationInput->table = $mapping->table;
                try {
                    $dbColumnList = $this->context->getStructure()->getColumns($table->name);
                } catch (InvalidArgumentException $e) {
                    $migrationInput->create = true;
                    $dbColumnList = [];
                }

                $databaseColumns = array_combine(
                    array_column($dbColumnList, 'name'),
                    $dbColumnList
                );
                foreach ($mapping->columns as $column) {
                    if (!isset($databaseColumns[$column->name])) {
                        $migrationInput->addNewColumn($column);
                    } elseif ($this->columnHasChanged($column, $databaseColumns[$column->name])) {
                        $migrationInput->addColumnUpdate($column);
                    }
                }

                foreach ($mapping->indexes as $key => $index) {
                    /** @var Index $index */
                    if (!$this->hasIndex($table->name, $index->name)) {
                        $migrationInput->addIndex($index);
                    }
                }
                foreach ($mapping->columns as $column) {
                    if (!$column instanceof JoinColumn) continue;
                    if (!$this->hasIndex($table->name, $column->name)) {
                        $migrationInput->addForeignKey($column);
                    }
                }
                $migrationInput->id = $mapping->id;
                yield $migrationInput;
            }
        }
    }

    private function hasIndex(string $table, string $name): bool
    {
        try {
            $indexes = $this->context->getConnection()
                ->query('SHOW INDEX FROM ' . $table)
                ->fetchAll();
        } catch (DriverException $e) {
            return false;
        }
        foreach ($indexes as $index) {
            if ($index['Column_name'] == $name) return true;
        }
        return false;
    }

    private function columnHasChanged(Column $column, array $databaseColumn): bool
    {
        return false;
    }

    public function makeMigration(string $entityDir, string $entityPrefix, string $migrationDirectory): string
    {
        $timeId = $this->getTimeId();
        $filePath = sprintf('%s/%s_new_migration_%s.php', realpath($migrationDirectory), $timeId, $timeId);
        $migrationClass = 'NewMigration' . $timeId;
        $class = new ClassType($migrationClass);
        $class->setExtends(AbstractMigration::class);
        $method = $class->addMethod('change')
            ->setPublic()
            ->setReturnType('void');
        $callbacks = [];
        foreach ($this->getMigrationInputIterator($entityDir, $entityPrefix) as $input) {
            if (!isset($callbacks[$input->table->name])) {
                $callbacks[$input->table->name] = [];
            }
            if ($input->create) {
                $callbacks[$input->table->name][] = fn() => $method
                    ->addBody('$table = $this->table(?, ?);', [$input->table->name, ['signed' => false]]);
            } else {
                $callbacks[$input->table->name][] = fn() => $method
                    ->addBody('$table = $this->table(?);', [$input->table->name]);
            }
            foreach ($input->getNewColumns() as $column) {
                if ($column instanceof JoinColumn) {
                    $type = 'integer';
                } elseif ($column instanceof Column) {
                    $type = $column->type;
                }
                $callbacks[$input->table->name][] = fn() => $method->addBody(
                    '$table->addColumn(?, ?, ?);',
                    [
                        $column->name,
                        $type,
                        $this->filterColumnOptions($column->options)
                    ]
                );
            }
            foreach ($input->getUpdateColumns() as $column) {
                if ($column instanceof JoinColumn) {
                    $type = 'integer';
                } elseif ($column instanceof Column) {
                    $type = $column->type;
                }
                $callbacks[$input->table->name][] = fn() => $method->addBody(
                    '$table->updateColumn(?, ?, ?);',
                    [
                        $column->name,
                        $type,
                        $this->filterColumnOptions($column->options)
                    ]
                );
            }
            // indexes
            foreach ($input->getIndexes() as $index) {
                $callbacks[$input->table->name][] = fn() => $method
                    ->addBody('$table->addIndex(?, ?);', [$index->name, $index->options]);
            }
            foreach ($input->getForeignKeys() as $foreignKey) {
                $callbacks[$input->table->name][] = fn() => $method
                    ->addBody(
                        '$table->addForeignKey(?, ?, ?, ?);',
                        [
                            $foreignKey->name,
                            $foreignKey->referencedTableName,
                            $foreignKey->referencedColumnName,
                            array_filter(
                                $foreignKey->options,
                                fn($v) => in_array($v, ['delete', 'update', 'constraint']),
                                ARRAY_FILTER_USE_KEY
                            )
                        ]
                    );
            }
            $callbacks[$input->table->name][] = fn() => $method->addBody('$table->save();');
        }
        $write = false;
        foreach ($callbacks as $tableCallbacks) {
            if (count($tableCallbacks) > 2) {
                $write = true;
                foreach ($tableCallbacks as $callback) {
                    call_user_func($callback);
                }
            }
        }
        if ($write) {
            file_put_contents(
                $filePath,
                "<?php \n" . $class
            );
            return $filePath;
        }
        return '';
    }

    private function getTimeId(): string
    {
        return (new \DateTime())->format('Ymd') . (str_pad(
                (time() - strtotime('today')) . '',
                5,
                '00000',
                STR_PAD_LEFT
            ));
    }

    private function filterColumnOptions(array $options)
    {
        return array_filter(
            $options,
            fn($v) => in_array(
                $v,
                [
                    'limit',
                    'length',
                    'default',
                    'null',
                    'after',
                    'comment',
                    'precision',
                    'scale',
                    'signed',
                    'values',
                    'identity',
                    'timezone',
                    'collation',
                    'encoding',
                ]
            ),
            ARRAY_FILTER_USE_KEY
        );
    }
}