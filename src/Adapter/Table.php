<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Audit\Adapter;

use Pop\Db\Record;

/**
 * Auditor table class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Table extends AbstractAdapter
{

    /**
     * Table class name
     * @var ?string
     */
    protected ?string $table = null;

    /**
     * Constructor
     *
     * Instantiate the table adapter object
     *
     * @param  string $table
     */
    public function __construct(string $table)
    {
        $this->setTable($table);
        $tableClass = $this->table;
        $db         = $tableClass::getDb();
        $tableName  = $tableClass::table();

        if (!($db->hasTable($tableName))) {
            $this->createTable($tableName);
        }
    }

    /**
     * Set the table
     *
     * @param  string $table
     * @return Table
     */
    public function setTable(string $table): Table
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the table
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception
     * @return Record
     */
    public function send(): Record
    {
        if ($this->action === null) {
            throw new Exception('The model state differences have not been resolved.');
        }
        if (($this->model === null) || ($this->modelId === null)) {
            throw new Exception('The model has not been set.');
        }

        $className = $this->table;
        $table     = new $className($this->prepareData());
        $table->save();

        return $table;
    }

    /**
     * Get model states
     *
     * @param  ?array $columns
     * @param  ?array $options
     * @return array
     */
    public function getStates(?array $columns = null, ?array $options = null): array
    {
        $tableClass = $this->table;
        $result     = ($columns !== null) ? $tableClass::findBy($columns, $options) : $tableClass::findAll($options);

        return array_map([$this, 'decodeState'], $result->toArray());
    }

    /**
     * Get model state by ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getStateById(int|string $id): array
    {
        $tableClass = $this->table;
        $record     = $tableClass::findById($id);
        return $this->decodeState($record->toArray());
    }

    /**
     * Get model state by model
     *
     * @param  string          $model
     * @param  int|string|null $modelId
     * @param  array           $columns
     * @return array
     */
    public function getStateByModel(string $model, int|string|null $modelId = null, array $columns = []): array
    {
        $columns['model']    = $model;
        if ($modelId !== null) {
            $columns['model_id'] = $modelId;
        }
        $tableClass = $this->table;
        $result     = $tableClass::findBy($columns);
        return array_map([$this, 'decodeState'], $result->toArray());
    }

    /**
     * Get model state by timestamp
     *
     * @param  int   $from
     * @param  ?int  $backTo
     * @param  array $columns
     * @return array
     */
    public function getStateByTimestamp(int $from, ?int $backTo = null, array $columns = []): array
    {
        $to = date('Y-m-d H:i:s', $from);
        $columns['timestamp'] = ($backTo !== null) ?
            ['BETWEEN', date('Y-m-d H:i:s', $backTo), $to] : ['<=', $to];

        $tableClass = $this->table;
        $result     = $tableClass::findBy($columns);
        return array_map([$this, 'decodeState'], $result->toArray());
    }

    /**
     * Get model state by date
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @param  array   $columns
     * @return array
     */
    public function getStateByDate(string $from, ?string $backTo = null, array $columns = []): array
    {
        if (!str_contains($from, ' ')) {
            $from .= ' 23:59:59';
        }
        if ($backTo !== null) {
            if (!str_contains($backTo, ' ')) {
                $backTo .= ' 00:00:00';
            }
            $columns['timestamp'] = ['BETWEEN', $backTo, $from];
        } else {
            $columns['timestamp'] = ['<=', $from];
        }
        $tableClass = $this->table;
        $result     = $tableClass::findBy($columns);
        return array_map([$this, 'decodeState'], $result->toArray());
    }

    /**
     * Decode the JSON-encoded old/new state fields of a result row
     *
     * @param  array $result
     * @return array
     */
    protected function decodeState(array $result): array
    {
        if (!empty($result['old']) && is_string($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (!empty($result['new']) && is_string($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        return $result;
    }

    /**
     * Get model snapshot by ID
     *
     * @param  int|string $id
     * @param  bool       $post
     * @return array
     */
    public function getSnapshot(int|string $id, bool $post = false): array
    {
        $tableClass = $this->table;
        $result     = $this->decodeState($tableClass::findById($id)->toArray());
        $snapshot   = [];

        if (!($post) && !empty($result['old'])) {
            $snapshot = $result['old'];
        } else if (($post) && !empty($result['new'])) {
            $snapshot = $result['new'];
        }

        return $snapshot;
    }

    /**
     * Create table in database
     *
     * @param  string $tableName
     * @return void
     */
    protected function createTable(string $tableName): void
    {
        $tableClass = $this->table;
        $db         = $tableClass::getDb();
        $schema     = $db->createSchema();
        $schema->create($tableName)
            ->int('id')->increment()
            ->int('user_id')
            ->varchar('username', 255)
            ->varchar('domain', 255)
            ->varchar('route', 255)
            ->varchar('method', 255)
            ->varchar('model', 255)->notNullable()
            ->int('model_id')->notNullable()
            ->varchar('action', 255)->notNullable()
            ->text('old')
            ->text('new')
            ->text('state')
            ->text('metadata')
            ->datetime('timestamp')->notNullable()
            ->primary('id');

        $db->query($schema);
    }

}
