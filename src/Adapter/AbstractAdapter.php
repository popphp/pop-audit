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
 * Auditor abstract adapter
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * Constants for action states
     * @var int
     */
    const CREATED = 'CREATED';
    const UPDATED = 'UPDATED';
    const DELETED = 'DELETED';

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
     * Get the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @return self
     */
    public function resolveDiff(array $old, array $new)
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
     * Send the results of the audit
     *
     * @return void
     */
    abstract public function send();

}