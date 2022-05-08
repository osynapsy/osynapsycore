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
 * Abstract Response
 */
abstract class Base
{
    protected $headers = [];
    protected $headerNames = [];
    protected $body = [];

    /**
     * Init response with the body type
     *
     * @param type $bodyType
     */
    public function __construct($bodyType = 'text/html')
    {
        $this->setContentType($bodyType);
    }

    public function addBufferToContent($path = null, $part = 'main')
    {
        $this->addContent($this->getBuffer($path) , $part);
    }

    /**
     * Method that add body to the response
     *
     * @param mixed $body
     * @param mixed $part
     * @param bool $checkUnique
     * @return mixed
     */
    public function addContent($body, $part = 'main', $checkUnique = false)
    {
        if ($checkUnique && !empty($this->body[$part]) && in_array($body, $this->body[$part])) {
            return;
        }
        if (!array_key_exists($part, $this->body)) {
            $this->body[$part] = [];
        }
        $this->body[$part][] = $body;
    }

    public function addValue($key, $value)
    {
        $this->body[$key] = $value;
    }

    public function clearCache()
    {
        $this->withHeader("Expires","Tue, 01 Jan 2000 00:00:00 GMT");
        $this->withHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
        $this->withHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $this->withHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $this->withHeader("Pragma","no-cache");
    }

    public function send($body, $part = 'main', $overwriteIfExists = false)
    {
        $this->addContent($body, $part, $overwriteIfExists);
    }

    public function exec()
    {
        $this->sendHeader();
        echo implode('',$this->body);
    }

    /**
     * Include a php page e return body string
     *
     * @param string $path
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public static function getBuffer($path = null, $controller = null)
    {
        $buffer = 1;
        if (!empty($path)) {
            if (!is_file($path)) {
                throw new \Exception('File '.$path.' not exists');
            }
            $buffer = include $path;
        }
        if ($buffer === 1) {
            $buffer = ob_get_contents();
            ob_clean();
        }
        return $buffer;
    }

    /**
     * Send header location to browser
     *
     * @param string $url
     */
    public function go($url)
    {
        header('Location: '.$url);
    }

    /**
     * Reset body part.
     *
     * @param mixed $part
     */
    public function resetContent($part = 'main')
    {
        $this->body[$part] = [];
    }

    /**
     * Set body of the response
     *
     * @param string $value
     */
    public function setContent($value)
    {
        $this->body = $value;
    }

    /**
     * Set body type of the response
     *
     * @param string $type
     */
    public function setContentType($type)
    {
        $this->withHeader('Content-Type', $type);
    }

    /**
     * Buffering of header
     *
     * @param string $key
     * @param string $value
     */
    public function withHeader($key, $value)
    {
        $caseInsesitiveKey = $this->caseInsesitiveKey($key);
        $this->headerNames[$caseInsesitiveKey] = $key;
        $this->headers[$key] = $value;
    }

    public function withAddedHeader($key, $value)
    {
        if (!$this->hasHeader($key)) {
            $this->withHeader($key, $value);
            return;
        }
        $originalKey = $this->headerNames[$this->caseInsesitiveKey($key)];
        $this->headers[$originalKey] .= ', '.$value;
    }

    protected function caseInsesitiveKey($key)
    {
        return strtolower($key);
    }

    /**
     * Check if key exists in header repository
     *
     * @param type $key key to search
     * @return bool
     */
    public function hasHeader($key) : bool
    {
        return array_key_exists($this->caseInsesitiveKey($key), $this->headerNames);
    }

    /**
     * Return the header line by key
     *
     * @param type $key
     * @return string
     */
    public function getHeaderLine($key) : ?string
    {
        return $this->hasHeader($key) ? $this->headers[$key] : null;
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public function getHeader($key)
    {
        return $this->hasHeader($key) ? explode(', ',$this->headers[$key]) : [];
    }

    /**
     * Set cookie
     *
     * @param string $valueId
     * @param string $value
     * @param unixdatetime $expiry
     */
    public static function cookie($valueId, $value, $expiry = null, $excludeThirdLevel = false)
    {
        if (headers_sent()) {
           return false;
        }
        $domain = $excludeThirdLevel ? self::getDomain() : self::getServerName();
        if (empty($expiry)) {
            $expiry = time() + (86400 * 365);
        }
        return setcookie($valueId, $value, $expiry, "/", $domain);
    }

    private static function getDomain()
    {
        $domainPart = explode('.', self::getServerName());
        if (count($domainPart) > 2){
           unset($domainPart[0]);
        }
        return '.'.implode('.', $domainPart);
    }

    private static function getServerName()
    {
        return filter_input(\INPUT_SERVER, 'SERVER_NAME');
    }

    /**
     * Send header buffer
     */
    protected function sendHeader()
    {
        if (!headers_sent()) {
            foreach ($this->headers as $key => $value) {
               header($key.': '.$value);
            }
        }
    }

    /**
     * Method for build response string
     * @abstract
     */
    abstract public function __toString();
}
