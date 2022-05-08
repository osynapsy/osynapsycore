<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Console;

use Osynapsy\Kernel\Loader;
use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;
use Osynapsy\Kernel;

/**
 * Description of Cron
 *
 * @author Peter
 */
class Cron
{
    private $argv;
    private $script;
    private $kernel;

    public function __construct(array $argv)
    {
        if (empty($argv)) {
            $this->raiseException('Non hai fornito un application path');
        }
        $this->script = array_shift($argv);
        $this->argv = $argv;
    }

    public function run()
    {
        $appConfiguration = $this->loadAppConfiguration($this->argv[0]);
        $cronJobs = $this->getCronJobs($appConfiguration);
        if (!empty($cronJobs)) {
            $this->exec($cronJobs, $appConfiguration);
        }
    }

    private function loadAppConfiguration($instancePath)
    {
        if (!is_dir($instancePath)) {
            $this->raiseException(sprintf('Il percorso %s non esiste', $instancePath));
        }
        $loader = new Loader($instancePath);
        return $loader->search('app');
    }

    private function getCronJobs($configuration)
    {
        if (empty($configuration) || !is_array($configuration)) {
            return;
        }
        $jobs = [];
        foreach($configuration as $app => $config) {
            if (!empty($config['cron'])) {
                $jobs[$app] = $config['cron'];
            }
        }
        return $jobs;
    }

    private function exec($jobs, $appConfiguration)
    {
        $this->kernel = new Kernel($this->argv[0]);
        $request = new Request();
        $request->set('app', $appConfiguration);
        foreach($jobs as $appId => $appJobs) {
            foreach($appJobs as $jobId => $jobController){
                $this->execJob($jobId , $appId, $jobController, $request);
            }
        }
    }

    private function execJob($jobId, $application, $controller, $request)
    {
        $job = new Route($jobId, null, $application, $controller);
        $request->set('page.route', $job);
        echo $this->kernel->runApplication($job, $request);
    }

    protected function raiseException($message)
    {
        throw new \Exception($message);
    }
}
