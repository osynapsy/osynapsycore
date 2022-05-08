<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core\Http;

/**
 * Description of RequestRaw
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class RequestRaw
{
	private $raw;
    private $server;

    public function __construct()
    {
		$this->server = $_SERVER;
        $data = sprintf(
			"%s %s %s\n",
			$this->server['REQUEST_METHOD'],
			$this->server['REQUEST_URI'],
			$this->server['SERVER_PROTOCOL']
		);
		foreach ($this->getHeaderList() as $name => $value) {
			$data .= $name . ': ' . $value . "\n";
		}
		$data .= "\n".$this->buildBody();

		$this->raw = $data;
	}

    private function buildBody()
    {
        if (empty($_POST)) {
            return '';
        }
        $post = [];
        foreach($_POST as $key => $value){
            $post[] = $key.'='.$value;
        }
        return implode('&',$post);
    }

    private function getHeaderList()
    {
		$headerList = [];
		foreach ($this->server as $key => $value) {
			if (preg_match('/^HTTP_/',$key)) {
				// add to list
				$headerList[$this->convertHeaderKey($key)] = $value;
			}
		}
		return $headerList;
	}

    /**
     * convert HTTP_HEADER_NAME to Header-Name
     *
     * @param string $key of php $_SERVER array
     * @return string
     */
    private function convertHeaderKey($key)
    {
        $httpHeaderKey = strtr(substr($key,5),'_',' ');
		$httpHeaderKey = ucwords(strtolower($httpHeaderKey));
		return strtr($httpHeaderKey,' ','-');
    }

    public function get()
    {
        return $this->raw;
    }

    public function __toString()
    {
        return $this->get;
    }
}
