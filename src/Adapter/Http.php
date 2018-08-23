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

        $data = [
            'user_id'   => $this->userId,
            'username'  => $this->username,
            'domain'    => $this->domain,
            'model'     => $this->model,
            'model_id'  => $this->modelId,
            'action'    => $this->action,
            'old'       => json_encode($this->original),
            'new'       => json_encode($this->modified),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->stream->setFields($data);
        $this->stream->send();

        return $this->stream;
    }

}