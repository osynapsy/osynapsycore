<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Assets;

use Osynapsy\Core\Mvc\Controller\BaseController;
use Osynapsy\Core\Kernel\KernelException;

class Loader extends BaseController
{
    protected $path;
    protected $basePath;

    public function init()
    {
        $this->path = $this->getParameter(0);
        $this->basePath = __DIR__ . '/../../../assets/';
    }

    public function indexAction()
    {
        return $this->getFile($this->basePath . $this->path);
    }

    private function getFile($filename)
    {
        if (!is_file($filename)) {
            throw new KernelException('Page not found', 404);
        }
        $this->copyFileToCache($this->getRequest()->get('page.url'), $filename);
        return $this->sendFile($filename);
    }

    private function copyFileToCache($webPath, $assetsPath)
    {
        if (file_exists($webPath)) {
            return true;
        }
        $path = explode('/', $webPath);
        $file = array_pop($path);
        $currentPath = './';
        $isFirst = true;
        foreach($path as $dir){
            if (empty($dir)) {
                continue;
            }
            if (!is_writeable($currentPath)) {
                return false;
            }
            $currentPath .= $dir.'/';
            //If first directory (__assets) not exists or isn't writable abort copy
            if ($isFirst === true && !is_writable($currentPath)) {
                return false;
            }
            $isFirst = false;
            if (file_exists($currentPath)) {
                continue;
            }
            mkdir($currentPath);
        }
        $currentPath .= $file;
        if (!is_writable($currentPath)) {
            return false;
        }
        return copy($assetsPath, $currentPath);
    }

    private function sendFile($filename)
    {
        $offset = 86400 * 7;
        // calc the string in GMT not localtime and add the offset
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        //output the HTTP header
        $this->getResponse()->withHeader('Expires', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
        switch($ext) {
            case 'js':
                $this->getResponse()->setContentType('application/javascript');
                break;
            default:
                $this->getResponse()->setContentType('text/'.$ext);
                break;
        }
        return file_get_contents($filename);
    }
}
