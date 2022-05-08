<?php
namespace Osynapsy\Core\Mvc\Model;

/**
 * Description of Mapper
 *
 * @author pietr
 */
class Mapper
{
    protected $fields;
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function map($fieldNameOnForm, $fieldNameOnRecord = null, $defaultValue = null, $type = 'string')
    {
        $formValue = isset($_REQUEST[$fieldNameOnForm]) ? $_REQUEST[$fieldNameOnForm] : null;
        $field = new Field($this, $fieldNameOnRecord, $fieldNameOnForm, $type, isset($_REQUEST[$fieldNameOnForm]));
        $field->setValue($formValue, $defaultValue);
        $this->fields[$field->html] = $field;
        return $field;
    }

    protected function getFields()
    {
        return $this->fields;
    }

    protected function getModel()
    {
        return $this->model;
    }

    protected function validateField($field)
    {
        try {
            $this->getValidator()->validate($field);
        } catch(\Exception $e) {
            $this->getException()->setErrorOnField($field, $e->getMessage());
        }
    }
}
