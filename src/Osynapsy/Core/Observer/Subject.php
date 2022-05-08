<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Observer;

/**
 * Implement Observer functionality into object which use Subject trait
 *
 * @author Peter
 */
trait Subject
{
    private $observers;
    private $state;

    /**
     * Added observer
     *
     * @param \SplObserver $observer
     */
    public function attach(\SplObserver $observer)
    {
         $this->getObservers()->attach($observer);
    }

    /**
     * Remove observer
     *
     * @param \SplObserver $observer
     */
    public function detach(\SplObserver $observer)
    {
        $this->getObservers()->detach($observer);
    }

    /**
     * Load observer from Request
     *
     * @return type
     */
    private function loadObserver()
    {
        $observerList = $this->getRequest()->get('observers');
        if (empty($observerList)) {
            return;
        }
        $observers = array_keys($observerList, str_replace('\\', ':', get_class($this)));
        try {
            foreach($observers as $observer) {
                $observerClass = '\\'.trim(str_replace(':','\\',$observer));
                $this->attach(new $observerClass());
            }
        } catch(\Error $e) {
        }
    }

    /**
     * Notify at all observers which object have changed state
     */
    public function notify()
    {
        foreach ($this->getObservers() as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Return current state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set current state and notify update to observes
     *
     * @param string $state
     */
    public function setState( $state )
    {
        $this->state = $state;
        $this->notify();
    }

    /**
     * Get storage of observers
     *
     * @return \SplObjectStorage()
     */
    protected function getObservers()
    {
        if (is_null($this->observers)) {
            $this->observers = new \SplObjectStorage();
        }
        return $this->observers;
    }
}
