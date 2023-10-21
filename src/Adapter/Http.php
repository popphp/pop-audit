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

use Pop\Http\Client;

/**
 * Auditor HTTP class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Http extends AbstractAdapter
{

    /**
     * Client to send the audit results
     * @var ?Client
     */
    protected ?Client $sendClient = null;

    /**
     * Client to fetch the audit results
     * @var ?Client
     */
    protected ?Client $fetchClient = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP adapter object
     *
     * @param Client  $sendClient
     * @param ?Client $fetchClient
     */
    public function __construct(Client $sendClient, ?Client $fetchClient = null)
    {
        $this->setSendClient($sendClient);
        if ($fetchClient !== null) {
            $this->setFetchClient($fetchClient);
        }
    }

    /**
     * Set the send stream
     *
     * @param  Client $sendClient
     * @return Http
     */
    public function setSendClient(Client $sendClient): Http
    {
        $this->sendClient = $sendClient;
        return $this;
    }

    /**
     * Set the fetch stream
     *
     * @param  Client $fetchClient
     * @return Http
     */
    public function setFetchClient(Client $fetchClient): Http
    {
        $this->fetchClient = $fetchClient;
        return $this;
    }

    /**
     * Get the send stream
     *
     * @return Client
     */
    public function getSendClient(): Client
    {
        return $this->sendClient;
    }

    /**
     * Get the fetch stream
     *
     * @return Client
     */
    public function getFetchClient(): Client
    {
        return $this->fetchClient;
    }

    /**
     * Get the fetched result
     *
     * @return mixed
     */
    public function getFetchedResult(): mixed
    {
        $resultResponse = null;

        if (($this->fetchClient->hasResponse()) && ($this->fetchClient->getResponse()->hasBody())) {
            $resultResponse = $this->fetchClient->getResponse()->getParsedResponse();
        }

        return $resultResponse;
    }

    /**
     * Determine if the adapter has a fetch stream
     *
     * @return bool
     */
    public function hasFetchClient(): bool
    {
        return ($this->fetchClient !== null);
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception|Client\Exception|Client\Handler\Exception|\Pop\Http\Exception
     * @return Client\Response
     */
    public function send(): Client\Response
    {
        if ($this->action === null) {
            throw new Exception('The model state differences have not been resolved.');
        }
        if (($this->model === null) || ($this->modelId === null)) {
            throw new Exception('The model has not been set.');
        }

        $this->sendClient->setData($this->prepareData());
        return $this->sendClient->send();
    }

    /**
     * Get model states
     *
     * @param  array $fields
     * @return array
     */
    public function getStates(array $fields = []): array
    {
        if (!empty($fields)) {
            $this->fetchClient->setData($fields);
        }
        $this->fetchClient->send();

        $results = $this->getFetchedResult();

        return (is_array($results)) ? $results : [];
    }

    /**
     * Get model state by ID
     *
     * @param  int|string $id
     * @param  bool       $asQuery
     * @return array
     */
    public function getStateById(int|string $id, bool $asQuery = false): array
    {
        $origUrl = $this->fetchClient->getRequest()->getUriString();

        if ($asQuery) {
            $this->fetchClient->addData('id', $id);
        } else {
            $this->fetchClient->getRequest()->getUri()->setUri($origUrl . '/' . $id);
        }

        $this->fetchClient->send();
        $result = $this->getFetchedResult();

        if (is_array($result) && !empty($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (is_array($result) && !empty($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        $this->fetchClient->getRequest()->getUri()->setUri($origUrl);

        return $result;
    }

    /**
     * Get model state by model
     *
     * @param  string          $model
     * @param  int|string|null $modelId
     * @return array
     */
    public function getStateByModel(string $model, int|string|null $modelId = null): array
    {
        $fields = [
            'filter' => [
                'model = ' . $model
            ]
        ];

        if ($modelId !== null) {
            $fields['filter'][] = 'model_id = ' . $modelId;
        }

        $this->fetchClient->setData($fields);
        $this->fetchClient->send();

        $results = $this->getFetchedResult();

        return (is_array($results)) ? $results : [];
    }

    /**
     * Get model state by timestamp
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @return array
     */
    public function getStateByTimestamp(string $from, ?string $backTo = null): array
    {
        $from = date('Y-m-d H:i:s', $from);

        if ($backTo !== null) {
            $backTo = date('Y-m-d H:i:s', $backTo);
        }

        $fields = [
            'filter' => [
                'timestamp <= ' . $from
            ]
        ];

        if ($backTo !== null) {
            $fields['filter'][] = 'timestamp >= ' . $backTo;
        }

        $this->fetchClient->setData($fields);
        $this->fetchClient->send();

        $results = $this->getFetchedResult();

        return (is_array($results)) ? $results : [];
    }

    /**
     * Get model state by date
     *
     * @param  string  $from
     * @param  ?string $backTo
     * @return array
     */
    public function getStateByDate(string $from, ?string $backTo = null): array
    {
        if (!str_contains($from, ' ')) {
            $from .= ' 23:59:59';
        }

        if ($backTo !== null) {
            if (!str_contains($backTo, ' ')) {
                $backTo .= ' 00:00:00';
            }
        }

        $fields = [
            'filter' => [
                'timestamp <= ' . $from
            ]
        ];

        if ($backTo !== null) {
            $fields['filter'][] = 'timestamp >= ' . $backTo;
        }

        $this->fetchClient->setData($fields);
        $this->fetchClient->send();

        $results = $this->getFetchedResult();

        return (is_array($results)) ? $results : [];
    }

    /**
     * Get model snapshot by ID
     *
     * @param  int|string $id
     * @param  bool       $post
     * @return array
     */
    public function getSnapshot(int|string $id, bool $post = false): array
    {
        $url = $this->fetchClient->getRequest()->getUriString();;
        $this->fetchClient->getRequest()->getUri()->setUri($url . '/' . $id);
        $this->fetchClient->send();

        $result = $this->getFetchedResult();

        if (is_array($result) && !empty($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (is_array($result) && !empty($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        $snapshot = [];

        if (!($post) && !empty($result['old'])) {
            $snapshot = $result['old'];
        } else if (($post) && !empty($result['new'])) {
            $snapshot = $result['new'];
        }

        $this->fetchClient->getRequest()->getUri()->setUri($url);

        return $snapshot;
    }

}