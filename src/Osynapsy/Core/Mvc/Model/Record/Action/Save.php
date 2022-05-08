<?php
namespace Osynapsy\Core\Mvc\Model\Record\Action;

use Osynapsy\Mvc\Model\ModelErrorException;
use Osynapsy\Mvc\Action\Base;

/**
 * Description of Save
 *
 * @author Pietro
 */
class Save extends Base
{
    const EVENT_AFTER_EXECUTE = 'afterExecute';
    const EVENT_AFTER_INSERT = 'afterInsert';
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    const EVENT_AFTER_UPLOAD = 'afterUpload';
    const EVENT_BEFORE_EXECUTE = 'beforeExecute';
    const EVENT_BEFORE_INSERT = 'beforeInsert';
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    const EVENT_BEFORE_UPLOAD = 'beforeUpload';

    public function __construct()
    {
        $this->setTrigger([self::EVENT_AFTER_EXECUTE], [$this, 'afterExecute']);
        $this->setTrigger([self::EVENT_AFTER_INSERT], [$this, 'afterInsert']);
        $this->setTrigger([self::EVENT_AFTER_UPDATE], [$this, 'afterUpdate']);
        $this->setTrigger([self::EVENT_AFTER_UPLOAD], [$this, 'afterUpload']);
    }

    public function execute()
    {
        try {
            $this->executeTrigger(self::EVENT_BEFORE_EXECUTE);
            $this->getModel()->save();
            $this->executeTrigger(self::EVENT_AFTER_EXECUTE);
        } catch (ModelErrorException $e) {
            $this->sendErrors($e->getErrors());
        } catch (\Exception $e) {
            $this->sendErrors(['alert' => $e->getMessage()]);
        }
    }

    public function afterExecute()
    {
        if ($this->getModel()->uploadOccurred) {
            $this->executeTrigger(self::EVENT_AFTER_UPLOAD);
            return;
        }
        if ($this->getModel()->behavior === 'insert') {
            $this->executeTrigger(self::EVENT_AFTER_INSERT);
            return;
        }
        $this->executeTrigger(self::EVENT_AFTER_UPDATE);
    }

    public function afterInsert()
    {
        $this->getResponse()->historyPushState($this->getModel()->getLastId());
        $this->getResponse()->pageRefresh();
    }

    public function afterUpdate()
    {
        $this->getResponse()->pageBack();
    }

    public function afterUpload()
    {
        $this->getResponse()->historyPushState($this->getModel()->getLastId());
        $this->getResponse()->pageRefresh();
    }

    private function sendErrors($errors)
    {
        foreach($errors as $fieldHtml => $error) {
            $this->getResponse()->error($fieldHtml, $error);
        }
    }
}
