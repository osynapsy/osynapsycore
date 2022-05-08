<?php
namespace Osynapsy\Core\Mvc\Model\Record\Action;

use Osynapsy\Helper\Net\UploadManager;
use Osynapsy\Mvc\Model\ModelErrorException;
use Osynapsy\Mvc\Action\Base;

/**
 * Description of Upload
 *
 * @author pietr
 */
class Upload extends Base
{
    protected $uploadSuccessful = 0;
    protected $uploadManager;

    public function execute()
    {
        try {
            $this->saveFiles();
            $this->getModel()->save();
            if ($this->getModel()->behavior === 'insert') {
                $this->afterInsert();
            }
            $this->getResponse()->pageRefresh();
        } catch (ModelErrorException $e) {
            $this->sendErrors($e->getErrors());
            $this->resetFileBoxFields();
        } catch (\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
            $this->resetFileBoxFields();
        }
    }

    protected function afterInsert()
    {
        $this->getResponse()->historyPushState($this->getModel()->getLastId());
    }

    protected function getModelField($fieldName)
    {
        $field = $this->getModel()->getField($fieldName);
        if (empty($field)) {
            throw new \Exception("Field {$fieldName} not found", 404);
        }
        return $field;
    }

    protected function getUploadManager()
    {
        if (empty($this->uploadManager)) {
            $this->uploadManager = new UploadManager();
        }
        return $this->uploadManager;
    }

    protected function resetFileBoxFields()
    {
        foreach (array_keys($_FILES) as $fieldName) {
            $this->getResponse()->jquery("#{$fieldName}")->val('')->exec();
        }
    }

    protected function saveFiles()
    {
        if (!is_array($_FILES)) {
            throw new \Exception('No files is uploaded', 204);
        }
        foreach (array_keys($_FILES) as $fieldName) {
            if (!empty($_FILES[$fieldName]) && !empty($_FILES[$fieldName]['name'])) {
                $this->saveFile($fieldName);
            }
        }
    }

    protected function saveFile($fieldName)
    {
        $field = $this->getModelField($fieldName);
        $field->value = $this->getUploadManager()->saveFile($field->html, $field->uploadDir);
        $field->readonly = false;
        $this->uploadSuccessful++;
    }

    private function sendErrors($errors)
    {
        foreach($errors as $fieldHtml => $error) {
            $this->getResponse()->error($fieldHtml, $error);
        }
    }
}
