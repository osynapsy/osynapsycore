<?php
namespace Osynapsy\Core\Http\Response;

use Osynapsy\Html\Helper\JQuery;

/**
 * Description of JsonOsynapsy
 *
 * @author Pietro
 */
class JsonOsynapsy extends Json
{
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

    public function jquery($selector)
    {
        return new JQuery($selector, $this);
    }

    public function js($cmd)
    {
        $this->message('command','execCode', str_replace(PHP_EOL,'\n',$cmd));
    }

    public function showModalAlertOnView($message, $title = 'Alert')
    {
        $this->js(sprintf("Osynapsy.modal.alert('%s','%s')", $title, $message));
    }

    public function showModalConfirmOnView($message, $actionOnConfirm, $title = 'Confirm')
    {
        $this->js(sprintf("Osynapsy.modal.confirm('%s','%s','%s')", $title, $message, $actionOnConfirm));
    }

    public function showModalWindowOnView($title, $url, $width = '640px', $height = '480px')
    {
        $this->js(sprintf("Osynapsy.modal.window('%s','%s','%s','%s')", $title, $url, $width, $height));
    }

    public function jsRefreshComponentOnView(array $components)
    {
        if (empty($components)) {
            return;
        }
        $strComponents = implode("','", $components);
        $this->js(sprintf("parent.Osynapsy.refreshComponents(['%s'])", $strComponents));
    }

    public function jsCloseModal()
    {
        $this->js("parent.$('#amodal').modal('hide');");
    }

    public function pageBack()
    {
        $this->go('back');
    }

    public function pageRefresh()
    {
        $this->go('refresh');
    }

    public function historyPushState($parameterToUrlAppend)
    {
        if (empty($parameterToUrlAppend)) {
            return;
        }
        $this->js("history.pushState(null,null,'{$parameterToUrlAppend}');");
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
}