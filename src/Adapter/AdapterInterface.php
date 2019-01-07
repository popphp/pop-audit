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
 * Auditor adapter interface
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
 */
interface AdapterInterface
{

    /**
     * Set the model name
     *
     * @param  string $model
     * @return self
     */
    public function setModel($model);

    /**
     * Set the model ID
     *
     * @param  int $modelId
     * @return self
     */
    public function setModelId($modelId);

    /**
     * Get the model name
     *
     * @return string
     */
    public function getModel();

    /**
     * Get the model ID
     *
     * @return int
     */
    public function getModelId();

    /**
     * Get the action
     *
     * @return string
     */
    public function getAction();

    /**
     * Get the original model state differences
     *
     * @return array
     */
    public function getOriginal();

    /**
     * Get the modified model state differences
     *
     * @return array
     */
    public function getModified();

    /**
     * Set the username
     *
     * @param  string $username
     * @return self
     */
    public function setUsername($username);

    /**
     * Set the user ID
     *
     * @param  int $userId
     * @return self
     */
    public function setUserId($userId);

    /**
     * Set the domain
     *
     * @param  string $domain
     * @return self
     */
    public function setDomain($domain);

    /**
     * Set the route
     *
     * @param  string $route
     * @return self
     */
    public function setRoute($route);

    /**
     * Set the method
     *
     * @param  string $method
     * @return self
     */
    public function setMethod($method);

    /**
     * Set the metadata
     *
     * @param  array $metadata
     * @return self
     */
    public function setMetadata(array $metadata);

    /**
     * Add to the metadata
     *
     * @param  string $name
     * @param  mixed $value
     * @return self
     */
    public function addMetadata($name, $value);

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get the user ID
     *
     * @return int
     */
    public function getUserId();

    /**
     * Get the domain
     *
     * @return string
     */
    public function getDomain();

    /**
     * Get the route
     *
     * @return string
     */
    public function getRoute();

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Determine if there is metadata
     *
     * @param  string $name
     * @return boolean
     */
    public function hasMetadata($name = null);

    /**
     * Get the metadata
     *
     * @param  string $name
     * @return mixed
     */
    public function getMetadata($name = null);

    /**
     * Set the differences in values between the model states (that have already been processed)
     *
     * @param  array $old
     * @param  array $new
     * @return self
     */
    public function setDiff(array $old, array $new);

    /**
     * Resolve the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @return self
     */
    public function resolveDiff(array $old, array $new);

    /**
     * Check if the model states are different
     *
     * @return boolean
     */
    public function hasDiff();

    /**
     * Send the results of the audit
     *
     * @return mixed
     */
    public function send();

    /**
     * Get model states
     *
     * @return array
     */
    public function getStates();

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    public function getStateById($id);

    /**
     * Get model state by model
     *
     * @param  string $model
     * @param  int    $modelId
     * @return array
     */
    public function getStateByModel($model, $modelId = null);

    /**
     * Get model state by timestamp
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    public function getStateByTimestamp($from, $backTo = null);

    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    public function getStateByDate($from, $backTo = null);

    /**
     * Get model snapshot by ID
     *
     * @param  int     $id
     * @param  boolean $post
     * @return array
     */
    public function getSnapshot($id, $post = false);

}