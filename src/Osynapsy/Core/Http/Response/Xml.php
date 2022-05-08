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
class Xml extends Base
{
    public function __construct()
    {
        parent::__construct('application/xml; charset=utf-8');
    }
    /**
     * Implements abstract method for build response
     *
     * @return json string
     */
    public function __toString()
    {
        $this->sendHeader();
        return $this->body;
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
     * Store a error message
     *
     * If recall without parameter return if errors exists.
     * If recall with only $oid parameter return if error $oid exists
     * If recall it with $oid e $err parameter set error $err on key $oid.
     *
     * @param string $objectId
     * @param string $errorMessage
     * @return type
     */
    public function error($objectId = null, $errorMessage = null)
    {
        if (is_null($objectId) && is_null($errorMessage)){
            return array_key_exists('errors',$this->body);
        }
        if (!is_null($objectId) && is_null($errorMessage)){
            return array_key_exists('errors', $this->body) && array_key_exists($objectId, $this->body['errors']);
        }
        if (function_exists('mb_detect_encoding') && !mb_detect_encoding($errorMessage, 'UTF-8', true)) {
            $errorMessage = \utf8_encode($errorMessage);
        }
        $this->message('errors', $objectId, $errorMessage);
    }

    /**
     * Store a list of errors
     *
     * @param array $errorList
     * @return void
     */
    public function errors(array $errorList)
    {
        foreach ($errorList as $error) {
            $this->error($error[0], $error[1]);
        }
    }

    /**
     * Store a error message alias
     *
     * If recall without parameter return if errors exists.
     * If recall with only $oid parameter return if error $oid exists
     * If recall it with $oid e $err parameter set error $err on key $oid.
     *
     * @param string $errorMessage
     * @return type
     */
    public function alertJs($errorMessage)
    {
        if (!empty($errorMessage)) {
            $this->error('alert', $errorMessage);
        }
        return $this;
    }

    /**
     * Prepare a goto message for FormController.js
     *
     * If $immediate = true dispatch of the response is immediate
     *
     * @param string $url
     * @param bool $immediate
     */
    public function go($url, $immediate = true)
    {
        $this->message('command', 'goto', $url);
        if ($immediate) {
            $this->dispatch();
        }
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
            $this->body[$typ] = array();
        }
        $this->body[$typ][] = array($act,$val);
    }

    public function js($cmd)
    {
        $this->message('command','execCode', str_replace(PHP_EOL,'\n',$cmd));
    }
}
