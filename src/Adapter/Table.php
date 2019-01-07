<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
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
        $this->table = $table;
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

        $data = [
            'user_id'   => $this->userId,
            'username'  => $this->username,
            'domain'    => $this->domain,
            'route'     => $this->route,
            'method'    => $this->method,
            'model'     => $this->model,
            'model_id'  => $this->modelId,
            'action'    => $this->action,
            'old'       => json_encode($this->original),
            'new'       => json_encode($this->modified),
            'metadata'  => json_encode($this->metadata),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $className = $this->table;
        $table     = new $className($data);
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
        $result = call_user_func_array($this->table . '::findById', ['id' => $id]);
        $r      = $result->toArray();

        if (!empty($r['old'])) {
            $r['old'] = json_decode($r['old'], true);
        }
        if (!empty($r['new'])) {
            $r['new'] = json_decode($r['new'], true);
        }

        return $r;
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

}