<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
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

}