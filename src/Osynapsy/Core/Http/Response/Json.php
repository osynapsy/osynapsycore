<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Http\Response;

/**
 * Implements Json response
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Json extends Base
{
    public function __construct()
    {
        parent::__construct('application/json; charset=utf-8');
    }
    /**
     * Implements abstract method for build response
     *
     * @return json string
     */
    public function __toString()
    {
        $this->sendHeader();
        return json_encode($this->body);
    }

    public function debug($msg)
    {
        $this->message('errors','alert',$msg);
        $this->dispatch();
    }

    /**
     * Dispatch immediatly response
     */
    public function dispatch()
    {
        ob_clean();
        $this->sendHeader();
        die(json_encode($this->body));
    }

    /**
     * Append a generic messagge to the response
     *
     * @param string $typ
     * @param string $act
     * @param string $val
     */
    public function message($typ, $act, $val)
    {
        if (!array_key_exists($typ, $this->body)){
            $this->body[$typ] = [];
        }
        $this->body[$typ][] = [$act, $val];
    }
}
