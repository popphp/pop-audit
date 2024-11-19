<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Auditor
{

    /**
     * Auditor adapter
     * @var ?AdapterInterface
     */
    protected ?AdapterInterface $adapter = null;

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
     * @return AdapterInterface
     */
    public function adapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Set user
     *
     * @param  ?string         $model
     * @param  int|string|null $modelId
     * @return Auditor
     */
    public function setModel(?string $model = null, int|string|null $modelId = null): Auditor
    {
        if ($model !== null) {
            $this->adapter->setModel($model);
        }
        if ($modelId !== null) {
            $this->adapter->setModelId($modelId);
        }

        return $this;
    }

    /**
     * Set user
     *
     * @param  ?string $username
     * @param  ?int    $userId
     * @return Auditor
     */
    public function setUser(?string $username = null, ?int $userId = null): Auditor
    {
        if ($username !== null) {
            $this->adapter->setUsername($username);
        }
        if ($userId !== null) {
            $this->adapter->setUserId($userId);
        }

        return $this;
    }

    /**
     * Set domain, route and method
     *
     * @param  ?string $domain
     * @param  ?string $route
     * @param  ?string $method
     * @return Auditor
     */
    public function setDomain(?string $domain = null, ?string $route = null, ?string $method = null): Auditor
    {
        if ($domain !== null) {
            $this->adapter->setDomain($domain);
        }
        if ($route !== null) {
            $this->adapter->setRoute($route);
        }
        if ($method !== null) {
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
    public function setMetadata(array $metadata): Auditor
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
    public function addMetadata(string $name, mixed $value): Auditor
    {
        $this->adapter->addMetadata($name, $value);
        return $this;
    }

    /**
     * Set state data
     *
     * @param  array $stateData
     * @return Auditor
     */
    public function setStateData(array $stateData): Auditor
    {
        $this->adapter->setStateData($stateData);
        return $this;
    }

    /**
     * Get state data
     *
     * @param  ?string $name
     * @return mixed
     */
    public function getStateData(?string $name = null): mixed
    {
        return $this->adapter->getStateData($name);
    }

    /**
     * Has state data
     *
     * @param  ?string $name
     * @return bool
     */
    public function hasStateData(?string $name = null): bool
    {
        return $this->adapter->hasStateData($name);
    }

    /**
     * Set the differences in values between the model states (that have already been processed)
     *
     * @param  array $old
     * @param  array $new
     * @return Auditor
     */
    public function setDiff(array $old = [], array $new = []): Auditor
    {
        $this->adapter->setDiff($old, $new);
        return $this;
    }

    /**
     * Resolve the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @param  bool  $state
     * @return Auditor
     */
    public function resolveDiff(array $old = [], array $new = [], bool $state = true): Auditor
    {
        $this->adapter->resolveDiff($old, $new, $state);
        return $this;
    }

    /**
     * Check if the model states are different
     *
     * @return bool
     */
    public function hasDiff(): bool
    {
        return $this->adapter->hasDiff();
    }

    /**
     * Send the results of the audit
     *
     * @param  ?array $old
     * @param  ?array $new
     * @param  bool $state
     * @return mixed
     */
    public function send(array $old = null, array $new = null, bool $state = true): mixed
    {
        if (($old !== null) && ($new !== null)) {
            $this->adapter->resolveDiff($old, $new, $state);
        }

        return ($this->adapter->hasDiff()) ? $this->adapter->send() : false;
    }

}
