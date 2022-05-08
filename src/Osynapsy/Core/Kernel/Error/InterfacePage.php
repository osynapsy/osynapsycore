<?php
namespace Osynapsy\Core\Kernel\Error;

/**
 * Description of InterfacePage
 *
 * @author Pietro
 */
interface InterfacePage
{
    public function get() : string;

    public function setComment($comment) : void;

    public function setMessage($message, $submessage = null) : void;

    public function setTrace(array $trace) : void;
}
