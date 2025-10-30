<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    2.0.3
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
     * @var ?string
     */
    protected ?string $model = null;

    /**
     * Model ID
     * @var int|string|null
     */
    protected int|string|null $modelId = null;

    /**
     * Action (created, updated, deleted)
     * @var ?string
     */
    protected ?string $action = null;

    /**
     * Original model state differences
     * @var array
     */
    protected array $original = [];

    /**
     * Modified model state differences
     * @var array
     */
    protected array $modified = [];

    /**
     * Final state data
     * @var array
     */
    protected array $stateData = [];

    /**
     * Username
     * @var ?string
     */
    protected ?string $username = null;

    /**
     * User ID
     * @var int|string|null
     */
    protected int|string|null $userId = null;

    /**
     * Domain
     * @var ?string
     */
    protected ?string $domain = null;

    /**
     * Route
     * @var ?string
     */
    protected ?string $route = null;

    /**
     * Method
     * @var ?string
     */
    protected ?string $method = null;

    /**
     * Metadata
     * @var array
     */
    protected array$metadata = [];

    /**
     * Set the model name
     *
     * @param  string $model
     * @return AbstractAdapter
     */
    public function setModel(string $model): AbstractAdapter
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the model ID
     *
     * @param  int|string $modelId
     * @return AbstractAdapter
     */
    public function setModelId(int|string $modelId): AbstractAdapter
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     * Get the model name
     *
     * @return string|null
     */
    public function getModel(): string|null
    {
        return $this->model;
    }

    /**
     * Get the model ID
     *
     * @return int|string|null
     */
    public function getModelId(): int|string|null
    {
        return $this->modelId;
    }

    /**
     * Get the action
     *
     * @return string|null
     */
    public function getAction(): string|null
    {
        return $this->action;
    }

    /**
     * Get the original model state differences
     *
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Get the modified model state differences
     *
     * @return array
     */
    public function getModified(): array
    {
        return $this->modified;
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return AbstractAdapter
     */
    public function setUsername(string $username): AbstractAdapter
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the user ID
     *
     * @param  int|string $userId
     * @return AbstractAdapter
     */
    public function setUserId(int|string $userId): AbstractAdapter
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Set the domain
     *
     * @param  string $domain
     * @return AbstractAdapter
     */
    public function setDomain(string $domain): AbstractAdapter
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Set the route
     *
     * @param  string $route
     * @return AbstractAdapter
     */
    public function setRoute($route): AbstractAdapter
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set the method
     *
     * @param  string $method
     * @return AbstractAdapter
     */
    public function setMethod(string $method): AbstractAdapter
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set the metadata
     *
     * @param  array $metadata
     * @return AbstractAdapter
     */
    public function setMetadata(array $metadata): AbstractAdapter
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Add to the metadata
     *
     * @param  string $name
     * @param  mixed $value
     * @return AbstractAdapter
     */
    public function addMetadata(string $name, mixed $value): AbstractAdapter
    {
        $this->metadata[$name] = $value;
        return $this;
    }

    /**
     * Get the username
     *
     * @return string|null
     */
    public function getUsername(): string|null
    {
        return $this->username;
    }

    /**
     * Get the user ID
     *
     * @return int|string|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userId;
    }

    /**
     * Get the domain
     *
     * @return string|null
     */
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * Get the route
     *
     * @return string|null
     */
    public function getRoute(): string|null
    {
        return $this->route;
    }

    /**
     * Get the method
     *
     * @return string|null
     */
    public function getMethod(): string|null
    {
        return $this->method;
    }

    /**
     * Determine if there is metadata
     *
     * @param  ?string $name
     * @return bool
     */
    public function hasMetadata(?string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->metadata[$name]);
        } else {
            return !empty($this->metadata);
        }
    }

    /**
     * Get the metadata
     *
     * @param  ?string $name
     * @return mixed
     */
    public function getMetadata(?string $name = null): mixed
    {
        if ($name !== null) {
            return (isset($this->metadata[$name])) ? $this->metadata[$name] : null;
        } else {
            return $this->metadata;
        }
    }

    /**
     * Set the final state data
     *
     * @param  array $state
     * @return AbstractAdapter
     */
    public function setStateData(array $state): AbstractAdapter
    {
        $this->stateData = $state;
        return $this;
    }

    /**
     * Get the final state
     *
     * @param  ?string $name
     * @return mixed
     */
    public function getStateData(?string $name = null): mixed
    {
        if ($name !== null) {
            return (isset($this->stateData[$name])) ? $this->stateData[$name] : null;
        } else {
            return $this->stateData;
        }
    }

    /**
     * Determine if there is final state data
     *
     * @param  ?string $name
     * @return bool
     */
    public function hasStateData(?string $name = null): bool
    {
        return ($name !== null) ? array_key_exists($name, $this->stateData) : !empty($this->stateData);
    }

    /**
     * Set the differences in values between the model states (that have already been processed)
     *
     * @param  array $old
     * @param  array $new
     * @return AbstractAdapter
     */
    public function setDiff(array $old = [], array $new = []): AbstractAdapter
    {
        $this->original = $old;
        $this->modified = $new;

        if (empty($old) && !empty($new)) {
            $this->action = AbstractAdapter::CREATED;
        } else if (empty($new) && !empty($old)) {
            $this->action = AbstractAdapter::DELETED;
        } else {
            $this->action = AbstractAdapter::UPDATED;
        }

        return $this;
    }

    /**
     * Resolve the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @param  bool  $state
     * @return AbstractAdapter
     */
    public function resolveDiff(array $old = [], array $new = [], bool $state = true): AbstractAdapter
    {
        if ($state) {
            $this->setStateData($new);
        }
        if (empty($old) && !empty($new)) {
            $this->modified = $new;
            $this->action   = AbstractAdapter::CREATED;
        } else if (empty($new) && !empty($old)) {
            $this->original = $old;
            $this->action   = AbstractAdapter::DELETED;
        } else {
            $keys = array_keys(array_diff($old, $new));
            foreach ($keys as $key) {
                $this->original[$key] = $old[$key];
                $this->modified[$key] = $new[$key];
            }
            $this->action = AbstractAdapter::UPDATED;
        }

        return $this;
    }

    /**
     * Check if the model states are different
     *
     * @return bool
     */
    public function hasDiff(): bool
    {
        return (($this->action !== null) && ($this->original !== $this->modified));
    }

    /**
     * Prepare data
     *
     * @param  bool $jsonEncode
     * @return array
     */
    public function prepareData(bool $jsonEncode = true): array
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
            'state'     => ($jsonEncode) ? json_encode($this->stateData) : $this->stateData,
            'metadata'  => ($jsonEncode) ? json_encode($this->metadata) : $this->metadata,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send the results of the audit
     *
     * @return mixed
     */
    abstract public function send(): mixed;

    /**
     * Get model states
     *
     * @return array
     */
    abstract public function getStates(): array;

    /**
     * Get model state by ID
     *
     * @param  int|string $id
     * @return array
     */
    abstract public function getStateById(int|string $id): array;

    /**
     * Get model state by model
     *
     * @param  string          $model
     * @param  int|string|null $modelId
     * @return array
     */
    abstract public function getStateByModel(string $model, int|string|null $modelId = null): array;

    /**
     * Get model state by timestamp
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @return array
     */
    abstract public function getStateByTimestamp(string $from, ?string $backTo = null): array;

    /**
     * Get model state by date
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @return array
     */
    abstract public function getStateByDate(string $from, ?string $backTo = null): array;

    /**
     * Get model snapshot by ID
     *
     * @param  int|string $id
     * @param  bool       $post
     * @return array
     */
    abstract public function getSnapshot(int|string $id, bool $post = false): array;

}
