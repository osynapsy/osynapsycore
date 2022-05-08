<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Kernel;

use Osynapsy\Core\DataStructure\Dictionary;

/**
 * This class is used from kernel to load xml configuration.
 * App configuration and Instance configuration.
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Loader
{
    private $repo;
    private $path;

    /**
     *
     * @param string $path Path of xml file to load
     */
    public function __construct($path)
    {
        $this->path = realpath($path);
        $this->repo = new Dictionary();
        $this->repo->set('configuration', $this->load());
        $this->loadAppConfiguration();
    }

    private function load()
    {
        $array = [];
        if (is_file($this->path)) {
            $array = $this->loadFile($this->path);
        } elseif (is_dir($this->path)) {
            $array = $this->loadDir($this->path);
        }
        return $array;
    }

    private function loadDir($path)
    {
        $files = scandir($path);
        $array = [];
        if (empty($files) || !is_array($files)) {
            return $array;
        }
        foreach ($files as $file){
            if (strpos($file,'.xml') === false) {
                continue;
            }
            $array = array_merge_recursive($array, $this->loadFile($path.'/'.$file));
        }
        return $array;
    }

    private function loadFile($path)
    {
        return function_exists('apcu_fetch') ? $this->loadFileFromCache($path) : $this->loadFileFromDisk($path);
    }

    protected function loadFileFromCache($path)
    {
        $keyId = 'config.file.'.sha1($path);
        $mtime = filemtime($path);
        $result = apcu_fetch($keyId);
        if ($result === false || empty($result[0]) || $mtime > $result[0]) {
            $result = [$mtime, $this->loadFileFromDisk($path)];
            apcu_store($keyId , $result);
        }
        return $result[1];
    }

    protected function loadFileFromDisk($path)
    {
        $xml = new \SimpleXMLIterator($path, null, true);
        return $this->parseXml($xml);
    }

    /**
     * Load application configuration
     *
     * @return void
     */
    private function loadAppConfiguration()
    {
        $apps = $this->repo->get('configuration.app');
        if (empty($apps)) {
            return;
        }
        $path = is_dir($this->path) ? $this->path : dirname($this->path);
        foreach($apps as $app => $conf) {
            if (empty($conf['path'])) {
                $conf['path'] = 'vendor';
            }
            $appPath = sprintf('%s/../%s/%s/etc/config.xml', $path, $conf['path'], str_replace("_", "/", $app));
            if (is_file($appPath)) {
                $this->repo->append('configuration.app.'.$app, $this->loadFile($appPath));
            }
        }
    }

    /**
     * Parse configuration xml file or xml fragment
     *
     * @param string $xml
     * @param array $tree
     * @return array
     */
    private function parseXml($xml, &$tree = [])
    {
        for($xml->rewind(); $xml->valid(); $xml->next() ) {
            $nodeKey = $xml->key();
            if (!array_key_exists($nodeKey, $tree)) {
                $tree[$nodeKey] = [];
            }
            $attributes = (array) $xml->current()->attributes();
            if ($xml->hasChildren()){
                $this->parseXml($xml->current(), $tree[$nodeKey]);
                continue;
            }
            if (empty($attributes)) {
               $tree[$nodeKey] = trim(strval($xml->current()));
               continue;
            }
            $tree[$nodeKey][] = ['@value' => \trim(\strval($xml->current()))] + $attributes['@attributes'];
        }
        return $tree;
    }

    /**
     * Get configuration key or branch
     *
     * @param string $key
     * @return mixed
     */
    public function get($key = '')
    {
        return $this->repo->get('configuration'.(empty($key) ? '' : ".{$key}"));
    }

    /**
     * Search branch in dictionary
     *
     * @param string $keySearch
     * @param string $searchPath
     * @param bool $debug
     * @return mixed
     */
    public function search($keySearch, $searchPath = null, $debug = false)
    {
        $fullPath = 'configuration';
        if (!empty($searchPath)) {
            $fullPath .= '.'.$searchPath;
        }
        if ($debug) {
            var_dump($fullPath);
        }
        return $this->repo->search($keySearch, $fullPath);
    }
}
