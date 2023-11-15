<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
interface AdapterInterface
{

    /**
     * Set the model name
     *
     * @param  string $model
     * @return AdapterInterface
     */
    public function setModel(string $model): AdapterInterface;

    /**
     * Set the model ID
     *
     * @param  int|string $modelId
     * @return AdapterInterface
     */
    public function setModelId(int|string $modelId): AdapterInterface;

    /**
     * Get the model name
     *
     * @return string|null
     */
    public function getModel(): string|null;

    /**
     * Get the model ID
     *
     * @return int|string|null
     */
    public function getModelId(): int|string|null;

    /**
     * Get the action
     *
     * @return string|null
     */
    public function getAction(): string|null;

    /**
     * Get the original model state differences
     *
     * @return array
     */
    public function getOriginal(): array;

    /**
     * Get the modified model state differences
     *
     * @return array
     */
    public function getModified(): array;

    /**
     * Set the username
     *
     * @param  string $username
     * @return AdapterInterface
     */
    public function setUsername(string $username): AdapterInterface;

    /**
     * Set the user ID
     *
     * @param  int|string $userId
     * @return AdapterInterface
     */
    public function setUserId(int|string $userId): AdapterInterface;

    /**
     * Set the domain
     *
     * @param  string $domain
     * @return AdapterInterface
     */
    public function setDomain(string $domain): AdapterInterface;

    /**
     * Set the route
     *
     * @param  string $route
     * @return AdapterInterface
     */
    public function setRoute(string $route): AdapterInterface;

    /**
     * Set the method
     *
     * @param  string $method
     * @return AdapterInterface
     */
    public function setMethod(string $method): AdapterInterface;

    /**
     * Set the metadata
     *
     * @param  array $metadata
     * @return AdapterInterface
     */
    public function setMetadata(array $metadata): AdapterInterface;

    /**
     * Add to the metadata
     *
     * @param  string $name
     * @param  mixed $value
     * @return AdapterInterface
     */
    public function addMetadata(string $name, mixed $value): AdapterInterface;

    /**
     * Get the username
     *
     * @return string|null
     */
    public function getUsername(): string|null;

    /**
     * Get the user ID
     *
     * @return int|string|null
     */
    public function getUserId(): int|string|null;

    /**
     * Get the domain
     *
     * @return string|null
     */
    public function getDomain(): string|null;

    /**
     * Get the route
     *
     * @return string|null
     */
    public function getRoute(): string|null;

    /**
     * Get the method
     *
     * @return string|null
     */
    public function getMethod(): string|null;

    /**
     * Determine if there is metadata
     *
     * @param  ?string $name
     * @return bool
     */
    public function hasMetadata(?string $name = null): bool;

    /**
     * Get the metadata
     *
     * @param  ?string $name
     * @return mixed
     */
    public function getMetadata(?string $name = null): mixed;

    /**
     * Set the final state data
     *
     * @param  array $state
     * @return AdapterInterface
     */
    public function setStateData(array $state): AdapterInterface;

    /**
     * Get the final state
     *
     * @param  ?string $name
     * @return mixed
     */
    public function getStateData(?string $name = null): mixed;

    /**
     * Determine if there is a final state
     *
     * @return bool
     */
    public function hasStateData(): bool;

    /**
     * Set the differences in values between the model states (that have already been processed)
     *
     * @param  array $old
     * @param  array $new
     * @return AdapterInterface
     */
    public function setDiff(array $old, array $new): AdapterInterface;

    /**
     * Resolve the differences in values between the model states
     *
     * @param  array $old
     * @param  array $new
     * @param  bool  $state
     * @return AdapterInterface
     */
    public function resolveDiff(array $old, array $new, bool $state = true): AdapterInterface;

    /**
     * Check if the model states are different
     *
     * @return bool
     */
    public function hasDiff(): bool;

    /**
     * Prepare data
     *
     * @param  bool $jsonEncode
     * @return array
     */
    public function prepareData(bool $jsonEncode = true): array;

    /**
     * Send the results of the audit
     *
     * @return mixed
     */
    public function send(): mixed;

    /**
     * Get model states
     *
     * @return array
     */
    public function getStates(): array;

    /**
     * Get model state by ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getStateById(int|string $id): array;

    /**
     * Get model state by model
     *
     * @param  string          $model
     * @param  int|string|null $modelId
     * @return array
     */
    public function getStateByModel(string $model, int|string|null $modelId = null): array;

    /**
     * Get model state by timestamp
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @return array
     */
    public function getStateByTimestamp(string $from, ?string $backTo = null): array;

    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    public function getStateByDate(string $from, ?string $backTo = null): array;

    /**
     * Get model snapshot by ID
     *
     * @param  int|string $id
     * @param  bool       $post
     * @return array
     */
    public function getSnapshot(int|string $id, bool $post = false): array;

}