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
 * Auditor HTTP class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class Http extends AbstractAdapter
{

    /**
     * Stream to send the audit results
     * @var \Pop\Http\Client\Stream
     */
    protected $stream = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP adapter object
     *
     * @param  \Pop\Http\Client\Stream $stream
     */
    public function __construct(\Pop\Http\Client\Stream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Get the stream
     *
     * @return \Pop\Http\Client\Stream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception
     * @return \Pop\Http\Client\Stream
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

        $this->stream->setFields($data);
        $this->stream->send();

        return $this->stream;
    }

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    public function getStateById($id)
    {

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

    }

    /**
     * Get model state by timestamp
     *
     * @param  string $from
     * @param  string $to
     * @return array
     */
    public function getStateByTimestamp($from, $to = null)
    {

    }


    /**
     * Get model state by date
     *
     * @param  string $from
     * @param  string $to
     * @return array
     */
    public function getStateByDate($from, $to = null)
    {

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

    }

}