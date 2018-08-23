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
namespace Pop\Audit;

use Pop\Audit\Adapter\AdapterInterface;

/**
 * Auditor class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class Auditor
{

    /**
     * Auditor adapter
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the auditor object
     *
     * @param  AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the adapter
     *
     * @return mixed
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Set user
     *
     * @param  string  $model
     * @param  int     $modelId
     * @return Auditor
     */
    public function setModel($model = null, $modelId = null)
    {
        if (null !== $model) {
            $this->adapter->setModel($model);
        }

        if (null !== $modelId) {
            $this->adapter->setModelId($modelId);
        }

        return $this;
    }

    /**
     * Set user
     *
     * @param  string  $username
     * @param  int     $userId
     * @return Auditor
     */
    public function setUser($username = null, $userId = null)
    {
        if (null !== $username) {
            $this->adapter->setUsername($username);
        }

        if (null !== $userId) {
            $this->adapter->setUserId($userId);
        }

        return $this;
    }

    /**
     * Send the results of the audit
     *
     * @param  array $old
     * @param  array $new
     * @return mixed
     */
    public function send(array $old = [], array $new = [])
    {
        $this->adapter->resolveDiff($old, $new);
        return $this->adapter->send();
    }

}