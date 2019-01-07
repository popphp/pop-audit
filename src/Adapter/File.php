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

/**
 * Auditor file class
 *
 * @category   Pop
 * @package    Pop\Audit
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.3
 */
class File extends AbstractAdapter
{

    /**
     * Folder to store the audit results
     * @var string
     */
    protected $folder = null;


    /**
     * File prefix
     * @var string
     */
    protected $prefix = 'pop-audit-';

    /**
     * Constructor
     *
     * Instantiate the file adapter object
     *
     * @param  string $folder
     * @param  string $prefix
     * @throws Exception
     */
    public function __construct($folder, $prefix = 'pop-audit-')
    {
        if (!file_exists($folder)) {
            throw new Exception('That folder does not exist.');
        }
        $this->folder = $folder;
        $this->prefix = $prefix;
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
     * Get the prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get ID from filename
     *
     * @param  string $filename
     * @throws Exception
     * @return string
     */
    public function getId($filename)
    {
        $filename = substr($filename, 0, strrpos($filename, '.'));
        $filename = substr($filename, 0, strrpos($filename, '-'));
        return substr($filename, (strrpos($filename, '-') + 1));
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
            'old'       => $this->original,
            'new'       => $this->modified,
            'metadata'  => $this->metadata,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $id       = md5($this->model . '-' . $this->modelId) . '-' .uniqid() . '-' . time();
        $filename = $this->prefix . $id . '.log';
        file_put_contents($this->folder . DIRECTORY_SEPARATOR . $filename, json_encode($data, JSON_PRETTY_PRINT));

        return $filename;
    }

    /**
     * Get model states
     *
     * @param  string $sort
     * @param  int    $limit
     * @param  int    $offset
     * @return array
     */
    public function getStates($sort = 'DESC', $limit = null, $offset = null)
    {
        $files     = scandir($this->folder);
        $fileNames = [];
        $results   = [];

        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $mtime = filemtime($this->folder . DIRECTORY_SEPARATOR . $file);
                $fileNames[$mtime] = $file;
            }
        }

        if ($sort == 'ASC') {
            ksort($fileNames, SORT_NUMERIC);
        } else {
            krsort($fileNames, SORT_NUMERIC);
        }

        if (null !== $limit) {
            $fileNames = array_slice($fileNames, (int)$offset, (int)$limit);
        }

        foreach ($fileNames as $fileName) {
            $results[$fileName] = $this->decode($fileName);
        }

        return $results;
    }

    /**
     * Get model state by ID
     *
     * @param  int $id
     * @return array
     */
    public function getStateById($id)
    {
        $files   = scandir($this->folder);
        $results = [];

        foreach ($files as $file) {
            if (strpos($file, $id) !== false) {
                $results[$file] = $this->decode($file);
                break;
            }
        }

        return $results;
    }

    /**
     * Get model state by model
     *
     * @param  string $model
     * @param  int    $modelId
     * @throws Exception
     * @return array
     */
    public function getStateByModel($model, $modelId = null)
    {
        if (null === $modelId) {
            throw new Exception('You must pass a model ID.');
        }

        $files   = scandir($this->folder);
        $id      = md5($model . '-' . $modelId);
        $results = [];

        foreach ($files as $file) {
            if (strpos($file, $id) !== false) {
                $results[$file] = $this->decode($file);
            }
        }

        return $results;
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
        $files   = scandir($this->folder);
        $results = [];

        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $mtime = filemtime($this->folder . DIRECTORY_SEPARATOR . $file);
                if (((null !== $backTo) && ($mtime <= $from) && ($mtime >= $backTo)) ||
                    ((null === $backTo) && ($mtime <= $from))) {
                    $results[$file] = $this->decode($file);
                }
            }
        }

        return $results;
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
        $results = [];

        if (strpos($from, ' ') === false) {
            $from .= ' 23:59:59';
        }

        $from = strtotime($from);

        if (null !== $backTo) {
            if (strpos($backTo, ' ') === false) {
                $backTo .= ' 00:00:00';
            }
            $backTo = strtotime($backTo);
        }

        $files = scandir($this->folder);
        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $mtime = filemtime($this->folder . DIRECTORY_SEPARATOR . $file);
                if (((null !== $backTo) && ($mtime <= $from) && ($mtime >= $backTo)) ||
                    ((null === $backTo) && ($mtime <= $from))) {
                    $results[$file] = $this->decode($file);
                }
            }
        }

        return $results;
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
         $result   = $this->getStateById($id);
         $result   = reset($result);
         $snapshot = [];

         if (!($post) && !empty($result['old'])) {
             $snapshot = $result['old'];
         } else if (($post) && !empty($result['new'])) {
             $snapshot = $result['new'];
         }

         return $snapshot;
     }

}