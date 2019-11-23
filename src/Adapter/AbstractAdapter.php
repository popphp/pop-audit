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
 * Auditor abstract adapter
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * Constants for action states
     * @var int
     */
    const CREATED = 'created';
    const UPDATED = 'updated';
    const DELETED = 'deleted';

    /**
     * Model name
     * @var string
     */
    protected $model = null;

    /**
     * Model ID
     * @var int
     */
    protected $modelId = null;

    /**
     * Action (created, updated, deleted)
     * @var string
     */
    protected $action = null;

    /**
     * Original model state differences
     * @var array
     */
    protected $original = [];

    /**
     * Modified model state differences
     * @var array
     */
    protected $modified = [];

    /**
     * Username
     * @var string
     */
    protected $username = null;

    /**
     * User ID
     * @var int
     */
    protected $userId = null;

    /**
     * Domain
     * @var string
     */
    protected $domain = null;

    /**
     * Route
     * @var string
     */
    protected $route = null;

    /**
     * Method
     * @var string
     */
    protected $method = null;

    /**
     * Metadata
     * @var array
     */
    protected $metadata = [];

    /**
     * Set the model name
     *
     * @param  string $model
     * @return self
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the model ID
     *
     * @param  int $modelId
     * @return self
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     * Get the model name
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the model ID
     *
     * @return int
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Get the action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the original model state differences
     *
     * @return array
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Get the modified model state differences
     *
     * @return array
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the user ID
     *
     * @param  int $userId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Set the domain
     *
     * @param  string $domain
     * @return self
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Set the route
     *
     * @param  string $route
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set the method
     *
     * @param  string $method
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set the metadata
     *
     * @param  array $metadata
     * @return self
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Add to the metadata
     *
     * @param  string $name
     * @param  mixed $value
     * @return self
     */
    public function addMetadata($name, $value)
    {
        $this->metadata[$name] = $value;
        return $this;
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the user ID
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Determine if there is metadata
     *
     * @param  string $name
     * @return boolean
     */
    public function hasMetadata($name = null)
    {
        if (null !== $name) {
            return isset($this->metadata[$name]);
        } else {
            return !empty($this->metadata);
        }
    }

    /**
     * Get the metadata
     *
     * @param  string $name
     * @return mixed
     */
    public function getMetadata($name = null)
    {
        if (null !== $name) {
            return (isset($this->metadata[$name])) ? $this->metadata[$name] : null;
        } else {
            return $this->metadata;
        }
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
        $this->original = $old;
        $this->modified = $new;

        if (empty($old) && !empty($new)) {
            $this->action = self::CREATED;
        } else if (empty($new) && !empty($old)) {
            $this->action = self::DELETED;
        } else {
            $this->action = self::UPDATED;
        }

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
        if (empty($old) && !empty($new)) {
            $this->modified = $new;
            $this->action   = self::CREATED;
        } else if (empty($new) && !empty($old)) {
            $this->original = $old;
            $this->action   = self::DELETED;
        } else {
            $keys = array_keys(array_diff($old, $new));
            foreach ($keys as $key) {
                $this->original[$key] = $old[$key];
                $this->modified[$key] = $new[$key];
            }
            $this->action = self::UPDATED;
        }

        return $this;
    }

    /**
     * Check if the model states are different
     *
     * @return boolean
     */
    public function hasDiff()
    {
        return ((null !== $this->action) && ($this->original !== $this->modified));
    }

    /**
     * Prepare data
     *
     * @param  boolean $jsonEncode
     * @return array
     */
    public function prepareData($jsonEncode = true)
    {
        return [
            'user_id'   => $this->userId,
            'username'  => $this->username,
            'domain'    => $this->domain,
            'route'     => $this->route,
            'method'    => $this->method,
            'model'     => $this->model,
            'model_id'  => $this->modelId,
            'action'    => $this->action,
            'old'       => ($jsonEncode) ? json_encode($this->original) : $this->original,
            'new'       => ($jsonEncode) ? json_encode($this->modified) : $this->modified,
            'metadata'  => ($jsonEncode) ? json_encode($this->metadata) : $this->metadata,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send the results of the audit
     *
     * @return mixed
     */
    abstract public function send();

    /**
     * Get model states
     *
     * @return array
     */
    abstract public function getStates();

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    abstract public function getStateById($id);

    /**
     * Get model state by model
     *
     * @param  string $model
     * @param  int    $modelId
     * @return array
     */
    abstract public function getStateByModel($model, $modelId = null);

    /**
     * Get model state by timestamp
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    abstract public function getStateByTimestamp($from, $backTo = null);

    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    abstract public function getStateByDate($from, $backTo = null);

    /**
     * Get model snapshot by ID
     *
     * @param  int     $id
     * @param  boolean $post
     * @return array
     */
    abstract public function getSnapshot($id, $post = false);
}