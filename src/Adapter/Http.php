<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
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

        if ($this->fetchClient->hasResponse()) {
            $response = $this->fetchClient->getResponse();
            if ($response->hasBody()) {
                $resultResponse = $response->getParsedResponse();
            }
        }

        return $resultResponse;
    }

    /**
     * Decode the JSON-encoded old/new state fields of a result row
     *
     * @param  array $result
     * @return array
     */
    protected function decodeState(array $result): array
    {
        foreach (['old', 'new'] as $field) {
            if (!empty($result[$field]) && is_string($result[$field])) {
                $decoded = json_decode($result[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result[$field] = $decoded;
                }
            }
        }

        return $result;
    }

    /**
     * Send the fetch request and return the fetched result as an array
     *
     * @return array
     */
    protected function fetchResults(): array
    {
        $this->fetchClient->send();
        $results = $this->getFetchedResult();

        return (is_array($results)) ? $results : [];
    }

    /**
     * Send the fetch request with a filter payload and return the fetched result as an array
     *
     * @param  array $filter
     * @return array
     */
    protected function fetchByFilter(array $filter): array
    {
        $this->fetchClient->setData(['filter' => $filter]);
        return $this->fetchResults();
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

        return $this->fetchResults();
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
        $origUrl = $this->fetchClient->getRequest()->getUri()->getFullUri();

        if ($asQuery) {
            $this->fetchClient->addData('id', $id);
        } else {
            $this->fetchClient->getRequest()->getUri()->setUri($origUrl . '/' . $id);
        }

        $this->fetchClient->send();
        $parsedResult = $this->getFetchedResult();

        $result = (is_array($parsedResult)) ? $parsedResult : [$parsedResult];
        $result = $this->decodeState($result);

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
        $filter = ['model = ' . $model];

        if ($modelId !== null) {
            $filter[] = 'model_id = ' . $modelId;
        }

        return $this->fetchByFilter($filter);
    }

    /**
     * Get model state by timestamp
     *
     * @param  int  $from
     * @param  ?int $backTo
     * @return array
     */
    public function getStateByTimestamp(int $from, ?int $backTo = null): array
    {
        $filter = ['timestamp <= ' . date('Y-m-d H:i:s', $from)];

        if ($backTo !== null) {
            $filter[] = 'timestamp >= ' . date('Y-m-d H:i:s', $backTo);
        }

        return $this->fetchByFilter($filter);
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

        $filter = ['timestamp <= ' . $from];

        if ($backTo !== null) {
            if (!str_contains($backTo, ' ')) {
                $backTo .= ' 00:00:00';
            }
            $filter[] = 'timestamp >= ' . $backTo;
        }

        return $this->fetchByFilter($filter);
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
        $url = $this->fetchClient->getRequest()->getUri()->getFullUri();
        $this->fetchClient->getRequest()->getUri()->setUri($url . '/' . $id);
        $this->fetchClient->send();

        $parsedResult = $this->getFetchedResult();

        $result = (is_array($parsedResult)) ? $parsedResult : [$parsedResult];
        $result = $this->decodeState($result);

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
