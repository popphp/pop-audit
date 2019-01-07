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

use Pop\Http\Client\Stream;

/**
 * Auditor HTTP class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
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
        $this->sendStream = $sendStream;
        if (null !== $fetchStream) {
            $this->fetchStream = $fetchStream;
        }
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

        $data = [
            'user_id'   => $this->userId,
            'username'  => $this->username,
            'domain'    => $this->domain,
            'route'     => $this->route,
            'method'    => $this->method,
            'model'     => $this->model,
            'model_id'  => $this->modelId,
            'action'    => $this->action,
            'old'       => json_encode($this->original),
            'new'       => json_encode($this->modified),
            'metadata'  => json_encode($this->metadata),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->sendStream->setFields($data);
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

        return json_decode($this->fetchStream->getBody(), true);
    }

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    public function getStateById($id)
    {
        $url = $this->fetchStream->getUrl();
        $this->fetchStream->setUrl($url . '/' . $id);
        $this->fetchStream->send();

        $r = json_decode($this->fetchStream->getBody(), true);
        if (!empty($r['old'])) {
            $r['old'] = json_decode($r['old'], true);
        }
        if (!empty($r['new'])) {
            $r['new'] = json_decode($r['new'], true);
        }

        $this->fetchStream->setUrl($url);

        return $r;
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

        return json_decode($this->fetchStream->getBody(), true);
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

        $r = json_decode($this->fetchStream->getBody(), true);

        return $r;
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

        $r = json_decode($this->fetchStream->getBody(), true);

        return $r;
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

        $r = json_decode($this->fetchStream->getBody(), true);

        if (!empty($r['old'])) {
            $r['old'] = json_decode($r['old'], true);
        }
        if (!empty($r['new'])) {
            $r['new'] = json_decode($r['new'], true);
        }

        $snapshot = [];

        if (!($post) && !empty($r['old'])) {
            $snapshot = $r['old'];
        } else if (($post) && !empty($r['new'])) {
            $snapshot = $r['new'];
        }

        $this->fetchStream->setUrl($url);

        return $snapshot;
    }

}