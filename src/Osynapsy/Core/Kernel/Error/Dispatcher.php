<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Kernel\Error;

use Osynapsy\Core\Kernel\Error\Page\Html as PageHtml;
use Osynapsy\Core\Kernel\KernelException;

/**
 * Description of ErrorDispatcher
 *
 * Class responsible for dispatching and rendering html of Osynapsy Kernel exception.
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Dispatcher
{
    private $httpStatusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        419 => 'Authentication Timeout',
        420 => 'Enhance Your Calm',
        420 => 'Method Failure',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        424 => 'Method Failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Redirect',
        451 => 'Unavailable For Legal Reasons',
        494 => 'Request Header Too Large',
        495 => 'Cert Error',
        496 => 'No Cert',
        497 => 'HTTP to HTTPS',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error', //Server Error
        501 => 'Not Implemented', //Server Error
        502 => 'Bad Gateway', //Server Error
        503 => 'Service Unavailable', //Server Error
        504 => 'Gateway Timeout', //Server Error
        505 => 'HTTP Version Not Supported', //Server Error
        506 => 'Variant Also Negotiates', //Server Error
        507 => 'Insufficient Storage', //Server Error
        508 => 'Loop Detected', //Server Error
        509 => 'Bandwidth Limit Exceeded', //Server Error
        510 => 'Not Extended', //Server Error
        511 => 'Network Authentication Required', //Server Error
        598 => 'Network read timeout error', //Server Error
        599 => 'Network connect timeout error' //Server Error
    ];
    private $request;
    private $response;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function dispatchException(\Exception $e)
    {
        switch($e->getCode()) {
            case '403':
            case '404':
                 $this->httpErrorFactory($e);
                break;
            case '501':
                $this->pageTraceError($e->getMessage());
                break;
            default :
                $this->pageTraceError($this->formatMessage($e), $e->getTrace());
                break;
        }
        return $this->get();
    }

    public function dispatchError(\Error $e)
    {
        $this->pageTraceError($this->formatMessage($e), $e->getTrace());
        return $this->get();
    }

    private function formatMessage(\Throwable $e)
    {
        $message = [$e->getCode() .' - '.$e->getMessage()];
        $message[] = 'Line ' . $e->getLine() . ' of file ' . $e->getFile();
        return implode(PHP_EOL, $message);
    }

    public function httpErrorFactory(\Exception $e)
    {
        ob_clean();
        http_response_code($e->getCode());
        $pageError = $this->htmlPageFactory();
        $pageError->setMessage($e->getMessage() . ' | '.$e->getCode(), method_exists($e, 'getInfoMessage') ? $e->getInfoMessage() : '');
        $this->response = $pageError->get();
    }

    public function pageTraceError($message, $trace = [])
    {
        ob_clean();
        $comments = [];
        if (empty($this->request->get('env.instance.debug'))) {
            $comments[] = trim($message).PHP_EOL;
            foreach ($trace as $step) {
                $comment = '';
                $comment .= (!empty($step['file']) ?  str_pad($step['file'], 80, ' ', STR_PAD_RIGHT) : '');
                $comment .= (!empty($step['line']) ? str_pad("line {$step['line']}", 20, ' ', STR_PAD_RIGHT) : '');
                $comment .= (!empty($step['class']) ? "{$step['class']}" : '');
                $comment .= (!empty($step['function']) ? "->{$step['function']}" : '');
                $comment .= (!empty($step['args']) ? "(".print_r($step['args'], true).")" : '');
                $comments[] = $comment;
            }
            $message = "<div>Internal server error</div>";
            $trace = [];
        }
        if (filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION')) {
             $this->pageTraceErrorText($message, $trace);
            return;
        }
        $pageError = $this->htmlPageFactory();
        $pageError->setMessage($message);
        $pageError->setTrace($trace);
        $pageError->setComment($comments);
        $this->response = $pageError->get();
    }

    private function htmlPageFactory()
    {
        return new PageHtml();
    }

    private function pageTraceErrorText($message, $trace = [])
    {
        $message .= PHP_EOL;
        foreach($trace as $step) {
            if (empty($step['file'])) {
                continue;
            }
            $message .= $step['line'].' - ';
            $message .= $step['file'].PHP_EOL;
        }
        $this->response = $message;
    }

    public function get()
    {
        return $this->response;
    }

    public function __toString()
    {
        return $this->get();
    }
}
