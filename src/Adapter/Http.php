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

use Pop\Http\Client\Stream;

/**
 * Auditor HTTP class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class Http extends AbstractAdapter
{

    /**
     * Stream to send the audit results
     * @var Stream
     */
    protected $sendStream = null;

    /**
     * Stream to fetch the audit results
     * @var Stream
     */
    protected $fetchStream = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP adapter object
     *
     * @param Stream $sendStream
     * @param Stream $fetchStream
     */
    public function __construct(Stream $sendStream, Stream $fetchStream = null)
    {
        $this->setSendStream($sendStream);
        if (null !== $fetchStream) {
            $this->setFetchStream($fetchStream);
        }
    }

    /**
     * Set the send stream
     *
     * @param  Stream $sendStream
     * @return Http
     */
    public function setSendStream(Stream $sendStream)
    {
        $this->sendStream = $sendStream;
        return $this;
    }

    /**
     * Set the fetch stream
     *
     * @param  Stream $fetchStream
     * @return Http
     */
    public function setFetchStream(Stream $fetchStream)
    {
        $this->fetchStream = $fetchStream;
        return $this;
    }

    /**
     * Get the send stream
     *
     * @return Stream
     */
    public function getSendStream()
    {
        return $this->sendStream;
    }

    /**
     * Get the fetch stream
     *
     * @return Stream
     */
    public function getFetchStream()
    {
        return $this->fetchStream;
    }

    /**
     * Get the fetched result
     *
     * @return mixed
     */
    public function getFetchedResult()
    {
        $resultResponse = null;

        if (($this->fetchStream->hasResponse()) && ($this->fetchStream->getResponse()->hasBody())) {
            $resultResponse = $this->fetchStream->getResponse()->getBody()->getContent();
            if ($this->fetchStream->getResponse()->hasHeader('Content-Type')) {
                if ($this->fetchStream->getResponse()->getHeader('Content-Type')->getValue() == 'application/json') {
                    $resultResponse = json_decode($resultResponse, true);
                } else if ($this->fetchStream->getResponse()->getHeader('Content-Type')->getValue() == 'application/x-www-form-urlencoded') {
                    parse_str($resultResponse, $resultResponse);
                }
            }
        }

        return $resultResponse;
    }

    /**
     * Determine if the adapter has a fetch stream
     *
     * @return boolean
     */
    public function hasFetchStream()
    {
        return (null !== $this->fetchStream);
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception
     * @return Stream
     */
    public function send()
    {
        if (null === $this->action) {
            throw new Exception('The model state differences have not been resolved.');
        }
        if ((null === $this->model) || (null === $this->modelId)) {
            throw new Exception('The model has not been set.');
        }

        $this->sendStream->setFields($this->prepareData());
        $this->sendStream->send();

        return $this->sendStream;
    }

    /**
     * Get model states
     *
     * @param  array $fields
     * @return array
     */
    public function getStates(array $fields = [])
    {
        if (!empty($fields)) {
            $this->fetchStream->setFields($fields);
        }
        $this->fetchStream->send();

        return $this->getFetchedResult();
    }

    /**
     * Get model state by ID
     *
     * @param  int     $id
     * @param  boolean $asQuery
     * @return array
     */
    public function getStateById($id, $asQuery = false)
    {
        $origUrl = $this->fetchStream->getUrl();

        if ($asQuery) {
            $this->fetchStream->setField('id', $id);
        } else {
            $this->fetchStream->setUrl($origUrl . '/' . $id);
        }

        $this->fetchStream->send();
        $result = $this->getFetchedResult();

        if (!empty($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (!empty($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        $this->fetchStream->setUrl($origUrl);

        return $result;
    }

    /**
     * Get model state by model
     *
     * @param  string $model
     * @param  int    $modelId
     * @return array
     */
    public function getStateByModel($model, $modelId = null)
    {
        $fields = [
            'filter' => [
                'model = ' . $model
            ]
        ];

        if (null !== $modelId) {
            $fields['filter'][] = 'model_id = ' . $modelId;
        }

        $this->fetchStream->setFields($fields);
        $this->fetchStream->send();

        return $this->getFetchedResult();
    }

    /**
     * Get model state by timestamp
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    public function getStateByTimestamp($from, $backTo = null)
    {
        $from = date('Y-m-d H:i:s', $from);

        if (null !== $backTo) {
            $backTo = date('Y-m-d H:i:s', $backTo);
        }

        $fields = [
            'filter' => [
                'timestamp <= ' . $from
            ]
        ];

        if (null !== $backTo) {
            $fields['filter'][] = 'timestamp >= ' . $backTo;
        }

        $this->fetchStream->setFields($fields);
        $this->fetchStream->send();

        return $this->getFetchedResult();
    }

    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $backTo
     * @return array
     */
    public function getStateByDate($from, $backTo = null)
    {
        if (strpos($from, ' ') === false) {
            $from .= ' 23:59:59';
        }

        if (null !== $backTo) {
            if (strpos($backTo, ' ') === false) {
                $backTo .= ' 00:00:00';
            }
        }

        $fields = [
            'filter' => [
                'timestamp <= ' . $from
            ]
        ];

        if (null !== $backTo) {
            $fields['filter'][] = 'timestamp >= ' . $backTo;
        }

        $this->fetchStream->setFields($fields);
        $this->fetchStream->send();

        return $this->getFetchedResult();
    }

    /**
     * Get model snapshot by ID
     *
     * @param  int     $id
     * @param  boolean $post
     * @return array
     */
    public function getSnapshot($id, $post = false)
    {
        $url = $this->fetchStream->getUrl();
        $this->fetchStream->setUrl($url . '/' . $id);
        $this->fetchStream->send();

        $result = $this->getFetchedResult();

        if (!empty($result['old'])) {
            $result['old'] = json_decode($result['old'], true);
        }
        if (!empty($result['new'])) {
            $result['new'] = json_decode($result['new'], true);
        }

        $snapshot = [];

        if (!($post) && !empty($result['old'])) {
            $snapshot = $result['old'];
        } else if (($post) && !empty($result['new'])) {
            $snapshot = $result['new'];
        }

        $this->fetchStream->setUrl($url);

        return $snapshot;
    }

}