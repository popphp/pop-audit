<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    2.0.3
 */
class File extends AbstractAdapter
{

    /**
     * Folder to store the audit results
     * @var ?string
     */
    protected ?string $folder = null;


    /**
     * File prefix
     * @var string
     */
    protected string $prefix = 'pop-audit-';

    /**
     * Constructor
     *
     * Instantiate the file adapter object
     *
     * @param  string $folder
     * @param  string $prefix
     * @throws Exception
     */
    public function __construct(string $folder, string $prefix = 'pop-audit-')
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
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * Get the prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get ID from filename
     *
     * @param  string $filename
     * @return string
     */
    public function getId(string $filename): string
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
     * @return array|false|null
     */
    public function decode(string $filename): array|false|null
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
    public function send(): string
    {
        if ($this->action === null) {
            throw new Exception('The model state differences have not been resolved.');
        }
        if (($this->model === null) || ($this->modelId === null)) {
            throw new Exception('The model has not been set.');
        }

        $id       = md5($this->model . '-' . $this->modelId) . '-' .uniqid() . '-' . time();
        $filename = $this->prefix . $id . '.log';
        file_put_contents(
            $this->folder . DIRECTORY_SEPARATOR . $filename,
            json_encode($this->prepareData(false), JSON_PRETTY_PRINT)
        );

        return $filename;
    }

    /**
     * Get model states
     *
     * @param  string $sort
     * @param  ?int   $limit
     * @param  ?int   $offset
     * @return array
     */
    public function getStates(string $sort = 'DESC', ?int $limit = null, ?int $offset = null): array
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

        if ($limit !== null) {
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
     * @param  int|string $id
     * @return array
     */
    public function getStateById(int|string $id): array
    {
        $files   = scandir($this->folder);
        $results = [];

        foreach ($files as $file) {
            if (str_contains($file, $id)) {
                $results[$file] = $this->decode($file);
                break;
            }
        }

        return $results;
    }

    /**
     * Get model state by model
     *
     * @param  string          $model
     * @param  int|string|null $modelId
     * @throws Exception
     * @return array
     */
    public function getStateByModel(string $model, int|string|null $modelId = null): array
    {
        if ($modelId === null) {
            throw new Exception('You must pass a model ID.');
        }

        $files   = scandir($this->folder);
        $id      = md5($model . '-' . $modelId);
        $results = [];

        foreach ($files as $file) {
            if (str_contains($file, $id)) {
                $results[$file] = $this->decode($file);
            }
        }

        return $results;
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
        $files   = scandir($this->folder);
        $results = [];

        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $mtime = filemtime($this->folder . DIRECTORY_SEPARATOR . $file);
                if ((($backTo !== null) && ($mtime <= $from) && ($mtime >= $backTo)) ||
                    (($backTo === null) && ($mtime <= $from))) {
                    $results[$file] = $this->decode($file);
                }
            }
        }

        return $results;
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
        $results = [];

        if (!str_contains($from, ' ')) {
            $from .= ' 23:59:59';
        }

        $from = strtotime($from);

        if ($backTo !== null) {
            if (!str_contains($backTo, ' ')) {
                $backTo .= ' 00:00:00';
            }
            $backTo = strtotime($backTo);
        }

        $files = scandir($this->folder);
        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $mtime = filemtime($this->folder . DIRECTORY_SEPARATOR . $file);
                if ((($backTo !== null) && ($mtime <= $from) && ($mtime >= $backTo)) ||
                    (($backTo === null) && ($mtime <= $from))) {
                    $results[$file] = $this->decode($file);
                }
            }
        }

        return $results;
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
