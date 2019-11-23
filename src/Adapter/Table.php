<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Audit\Adapter;

/**
 * Auditor table class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class Table extends AbstractAdapter
{

    /**
     * Table class name
     * @var string
     */
    protected $table = null;

    /**
     * Constructor
     *
     * Instantiate the table adapter object
     *
     * @param  string $table
     */
    public function __construct($table)
    {
        $this->setTable($table);
        $db        = call_user_func($this->table . '::getDb');
        $tableName = call_user_func($this->table . '::table');

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
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception
     * @return \Pop\Db\Record
     */
    public function send()
    {
        if (null === $this->action) {
            throw new Exception('The model state differences have not been resolved.');
        }
        if ((null === $this->model) || (null === $this->modelId)) {
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
     * @param  array $columns
     * @param  array $options
     * @return array
     */
    public function getStates(array $columns = null, array $options = null)
    {
        if (null !== $columns) {
            $result = call_user_func_array($this->table . '::findBy', ['columns' => $columns, 'options' => $options]);
        } else {
            $result = call_user_func_array($this->table . '::findAll', ['options' => $options]);
        }

        return $result->toArray();
    }

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    public function getStateById($id)
    {
        $record = call_user_func_array($this->table . '::findById', ['id' => $id]);
        $result = $record->toArray();

        if (!empty($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (!empty($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        return $result;
    }

    /**
     * Get model state by model
     *
     * @param  string $model
     * @param  int    $modelId
     * @param  array  $columns
     * @return array
     */
    public function getStateByModel($model, $modelId = null, array $columns = [])
    {
        $columns['model']    = $model;
        if (null !== $modelId) {
            $columns['model_id'] = $modelId;
        }
        $result = call_user_func_array($this->table . '::findBy', [$columns]);
        return $result->toArray();
    }

    /**
     * Get model state by timestamp
     *
     * @param  string $from
     * @param  string $backTo
     * @param  array  $columns
     * @return array
     */
    public function getStateByTimestamp($from, $backTo = null, array $columns = [])
    {
        $columns['timestamp<='] = date('Y-m-d H:i:s', $from);
        if (null !== $backTo) {
            $columns['timestamp>='] = date('Y-m-d H:i:s', $backTo);
        }
        $result = call_user_func_array($this->table . '::findBy', [$columns]);
        return $result->toArray();
    }

    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $backTo
     * @param  array  $columns
     * @return array
     */
    public function getStateByDate($from, $backTo = null, array $columns = [])
    {
        if (strpos($from, ' ') === false) {
            $from .= ' 23:59:59';
        }
        $columns['timestamp<='] = $from;
        if (null !== $backTo) {
            if (strpos($backTo, ' ') === false) {
                $backTo .= ' 00:00:00';
            }
            $columns['timestamp>='] = $backTo;
        }
        $result = call_user_func_array($this->table . '::findBy', [$columns]);
        return $result->toArray();
    }

    /**
     * Get model snapshot by ID
     *
     * @param  int     $id
     * @param  boolean $post
     * @return array
     */
    public function getSnapshot($id, $post = false)
    {
        $result   = call_user_func_array($this->table . '::findById', ['id' => $id]);
        $snapshot = [];

        if (!($post) && !empty($result->old)) {
            $snapshot = json_decode($result->old, true);
        } else if (($post) && !empty($result->new)) {
            $snapshot = json_decode($result->new, true);
        }

        return $snapshot;
    }

    /**
     * Create table in database
     *
     * @param  string $tableName
     * @return void
     */
    protected function createTable($tableName)
    {
        $db     = call_user_func($this->table . '::getDb');
        $schema = $db->createSchema();
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
            ->text('metadata')
            ->datetime('timestamp')->notNullable()
            ->primary('id');

        $db->query($schema);
    }

}
