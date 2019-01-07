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
namespace Pop\Audit;

use Pop\Audit\Adapter\AdapterInterface;

/**
 * Auditor class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
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
     * Set domain, route and method
     *
     * @param  string  $domain
     * @param  string  $route
     * @param  string  $method
     * @return Auditor
     */
    public function setDomain($domain = null, $route = null, $method = null)
    {
        if (null !== $domain) {
            $this->adapter->setDomain($domain);
        }
        if (null !== $route) {
            $this->adapter->setRoute($route);
        }
        if (null !== $method) {
            $this->adapter->setMethod($method);
        }
        return $this;
    }

    /**
     * Set the metadata
     *
     * @param  array $metadata
     * @return Auditor
     */
    public function setMetadata(array $metadata)
    {
        $this->adapter->setMetadata($metadata);
        return $this;
    }

    /**
     * Add to the metadata
     *
     * @param  string $name
     * @param  mixed $value
     * @return Auditor
     */
    public function addMetadata($name, $value)
    {
        $this->adapter->addMetadata($name, $value);
        return $this;
    }


    /**
     * Set the differences in values between the model states (that have already been processed)
     *
     * @param  array $old
     * @param  array $new
     * @return self
     */
    public function setDiff(array $old = [], array $new = [])
    {
        $this->adapter->setDiff($old, $new);
        return $this;
    }

    /**
     * Resolve the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @return self
     */
    public function resolveDiff(array $old = [], array $new = [])
    {
        $this->adapter->resolveDiff($old, $new);
        return $this;
    }

    /**
     * Check if the model states are different
     *
     * @return boolean
     */
    public function hasDiff()
    {
        return $this->adapter->hasDiff();
    }

    /**
     * Send the results of the audit
     *
     * @param  array $old
     * @param  array $new
     * @return mixed
     */
    public function send(array $old = null, array $new = null)
    {
        if ((null !== $old) && (null !== $new)) {
            $this->adapter->resolveDiff($old, $new);
        }

        return ($this->adapter->hasDiff()) ? $this->adapter->send() : false;
    }

}