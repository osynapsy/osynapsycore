<?php
namespace Osynapsy\Core\Mvc\Model\Action;

use Osynapsy\Mvc\Action\Base;

/**
 * Description of ModelDelete action;
 *
 * @author Pietro
 */
class Delete extends Base
{
    public function __construct()
    {
        $this->setTrigger(['afterDelete'], [$this, 'afterDelete']);
    }

    public function execute()
    {
        try {
            $this->executeTrigger('beforeDelete');
            $this->getModel()->delete();
            $this->executeTrigger('afterDelete');
        } catch(\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
        }
    }

    public function afterDelete()
    {
        $this->getResponse()->go('back');
    }
}
