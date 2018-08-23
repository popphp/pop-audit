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
 * Auditor adapter interface
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
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
     * Get the differences in values between the model states
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

}