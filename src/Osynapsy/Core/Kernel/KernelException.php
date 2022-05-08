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

/**
 * Description of KernelException
 *
 * @author Peter
 */
class KernelException extends \Exception
{
    private $submessage;

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setInfoMessage($message)
    {
        $this->submessage = $message;
    }

    public function getInfoMessage()
    {
        return $this->submessage;
    }
}
