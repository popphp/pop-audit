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
 * Auditor file class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class File extends AbstractAdapter
{

    /**
     * Folder to store the audit results
     * @var string
     */
    protected $folder = null;

    /**
     * Constructor
     *
     * Instantiate the file adapter object
     *
     * @param  string $folder
     * @throws Exception
     */
    public function __construct($folder)
    {
        if (!file_exists($folder)) {
            throw new Exception('That folder does not exist.');
        }
        $this->folder = $folder;
    }

    /**
     * Get the folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Decode the audit file
     *
     * @param  string $filename
     * @throws Exception
     * @return array
     */
    public function decode($filename)
    {
        if (!file_exists($this->folder . DIRECTORY_SEPARATOR . $filename)) {
            throw new Exception('That audit file does not exist.');
        }

        return json_decode(file_get_contents($this->folder . DIRECTORY_SEPARATOR . $filename), true);
    }

    /**
     * Send the results of the audit
     *
     * @throws Exception
     * @return string
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
            'old'       => $this->original,
            'new'       => $this->modified,
            'metadata'  => $this->metadata,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $filename = 'pop-audit-' . time() . '.log';
        file_put_contents($this->folder . DIRECTORY_SEPARATOR . $filename, json_encode($data, JSON_PRETTY_PRINT));

        return $filename;
    }

}