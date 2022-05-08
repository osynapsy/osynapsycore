<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Mvc\Model;

use Osynapsy\Mvc\Model\Field as ModelField;
use Osynapsy\Helper\Net\UploadManager;

/**
 * The abstract class Model implement base methods for manage mapping
 * of the html fields on db fields.
 *
 * @author Pietro Celeste <p.celete@osynapsy.org>
 */
abstract class Model implements InterfaceModel
{
    const ACTION_AFTER_INSERT_HISTORY_PUSH_STATE = 'historyPushState';
    const ACTION_AFTER_EXEC_BACK = 'back';
    const ACTION_AFTER_EXEC_NONE = false;
    const ACTION_AFTER_EXEC_REFRESH = 'refresh';
    const ERROR_MESSAGES = [
        'email' => 'Il campo <fieldname> non contiene un indirizzo mail valido.',
        'fixlength' => 'Il campo <fieldname> solo valori con lunghezza pari a ',
        'integer' => 'Il campo <fieldname> accetta solo numeri interi.',
        'maxlength' => 'Il campo <fieldname> accetta massimo ',
        'minlength' => 'Il campo <fieldname> accetta minimo ',
        'notnull' => 'Il campo <fieldname> è obbligatorio.',
        'numeric' => 'Il campo <fieldname> accetta solo valori numerici.',
        'unique' => '<value> è già presente in archivio.'
    ];

    protected $controller = null;
    protected $db;
    protected $table;
    protected $sequence;
    protected $actions = [];
    protected $fields = [];
    protected $values = [];
    protected $softdelete;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->db = $this->getController()->getDb();
        $this->setAfterAction($this->getController()->getRequest()->get('page.url'), 'back', 'back');
        $this->init();
        if (empty($this->table)) {
            throw new \Exception('Model table is empty');
        }
    }

    abstract protected function init();

    public function map($htmlField, $dbField = null, $value = null, $type = 'string') : ModelField
    {
        $requestValue = isset($_REQUEST[$htmlField]) ? $_REQUEST[$htmlField] : null;
        $modelField = new ModelField($this, $dbField, $htmlField, $type);
        $modelField->setValue($requestValue, $value);
        return $this->fields[$modelField->html] = $modelField;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getResponse()
    {
        return $this->getController()->getResponse();
    }

    protected function setAfterAction($insert, $update, $delete)
    {
        $this->actions = [
            'after-insert' => $insert ?? $this->actions['after-insert'],
            'after-update' => $update ?? $this->actions['after-update'],
            'after-delete' => $delete ?? $this->actions['after-delete']
        ];
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    public function setTable($table, $sequence = null)
    {
        $this->table = $table;
        $this->sequence = $sequence;
    }

    public function find()
    {
        $where = $this->whereConditionFactory();
        if (!empty($where)) {
            $this->values = $this->getDb()->selectOne($this->table, $where);
            $this->assocData();
        }
    }

    protected function whereConditionFactory()
    {
        $result = [];
        foreach ($this->fields as $field) {
            if ($field->isPkey()) {
                $result[$field->name] = $field->value;
            }
        }
        return array_filter($result , function($value) { return ($value !=='0' && !empty($value)); });
    }

    public function assocData()
    {
        if (is_array($this->values)) {
            foreach ($this->fields as $f) {
               if (!array_key_exists($f->html, $_REQUEST) && array_key_exists($f->name, $this->values)) {
                    $_REQUEST[ $f->html ] = $this->values[ $f->name ];
                }
            }
        }
    }

    public function insert($values, $where = null)
    {
        $this->addError($this->beforeInsert());
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        $lastId = null;
        if ($this->sequence && is_array($where) && count($where) == 1) {
            $lastId = $values[$where[0]] = $this->getIdFromSequence($this->sequence);
            $this->getDb()->insert($this->table, $values);
        } else {
            $lastId = $this->getDb()->insert($this->table, $values);
        }
        $this->afterInsert($lastId);
        $this->execAfterAction('after-insert', $lastId);
    }

    protected function getIdFromSequence($sequence)
    {
        return is_callable($sequence) ? $sequence() : $this->getDb()->findOne("SELECT {$this->sequence}.nextval FROM DUAL");
    }

    public function update($values, $where)
    {
        $this->addError($this->beforeUpdate());
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        $this->getDb()->update($this->table, $values, $where);
        $this->afterUpdate();
        $this->execAfterAction('after-update');
    }

    public function delete()
    {
        $this->addError($this->beforeDelete());
        $where = $this->whereConditionFactory();
        if (empty($where)) {
            return;
        }
        if (!empty($this->softdelete)) {
            $this->getDb()->update($this->table, $this->softdelete, $where);
        } else {
            $this->getDb()->delete($this->table, $where);
        }
        $this->afterDelete();
        $this->execAfterAction('after-delete');
    }

    protected function execAfterAction($actionId, $lastId = null)
    {
        switch ($this->actions[$actionId]) {
            case self::ACTION_AFTER_EXEC_NONE:
                break;
            case self::ACTION_AFTER_INSERT_HISTORY_PUSH_STATE:
                $this->getResponse()->js("history.pushState(null,null,'{$lastId}');");
                break;
            case self::ACTION_AFTER_EXEC_BACK:
            case self::ACTION_AFTER_EXEC_REFRESH:
                $this->getResponse()->go($this->actions[$actionId]);
                break;
            default:
                $this->getResponse()->go($this->actions[$actionId].$lastId);
                break;
        }
    }

    protected function addFieldError($errorId, $field, $postfix = '')
    {
        $error = str_replace(['<fieldname>', '<value>'], ['<!--'.$field->html.'-->', $field->value], self::ERROR_MESSAGES[$errorId].$postfix);
        $this->addError($error, $field->html);
    }

    protected function addError($errorMessage, $target = 'alert')
    {
        if (!empty($errorMessage)) {
            $this->getController()->getResponse()->error($target, $errorMessage);
        }
    }

    /**
     * Save the record on database after record values validation.
     *
     * @return void
     */
    public function save() : void
    {
        //Recall before exec method with arbirtary code
        $this->addError($this->beforeExec());
        //Init arrays
        $keys = $values = $where = [];
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->fields as $field) {
            $value = $field->value;
            //Check if value respect rule
            $this->validateFieldValue($field, $value);
            if (in_array($field->type, ['file', 'image'])) {
                $value = $this->grabUploadedFile($field);
            }
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$field->readonly) {
                $values[$field->name] = $value;
            }
            //If field isn't primary key skip key assignment
            if (!$field->isPkey()) {
                continue;
            }
            //Add field to keys list
            $keys[] = $field->name;
            //If field has value assign field to where condition
            if (!empty($value)) {
                $where[$field->name] = $value;
            }
        }
        //If occurred some error stop db updating
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        //If where condition is empty execute db insert else execute a db update
        if (empty($where)) {
            $this->insert($values, $keys);
        } else {
            $this->update($values, $where);
        }
        //Recall after exec method with arbirtary code
        $this->afterExec();
    }

    /**
     * This method validate value of field.
     *
     * @param ModelField $field
     * @param type $value
     */
    protected function validateFieldValue(ModelField $field, $value)
    {
        if (!$field->isNullable()) {
            $this->validateNotNullValue($field, $value);
        }
        if ($field->isUnique() && $value) {
            $this->validateUniqueValue($field, $value);
        }
        $this->validateValueLength($field, $value);
        switch ($field->type) {
            case 'float':
            case 'money':
            case 'numeric':
            case 'number':
                $this->validateFloatValue($field, $value);
                break;
            case 'integer':
            case 'int':
                $this->validateIntegerValue($field, $value);
                break;
            case 'email':
                $this->validateEmailAddressValue($field, $value);
                break;
        }
    }

    protected function validateNotNullValue(ModelField $field, $value)
    {
        if ($value !== '0' && empty($value)) {
            $this->addFieldError('notnull', $field);
        }
    }

    protected function validateUniqueValue(ModelField $field, $value)
    {
        $sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s = ?", $this->table, $field->name);
        $nOccurence = $this->getDb()->findOne($sql, [$value]);
        if (!empty($nOccurence)) {
            $this->addFieldError('unique', $field);
        }
    }

    protected function validateValueLength(ModelField $field, $value)
    {
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($value) > $field->maxlength)) {
            $this->addFieldError('maxlength', $field, $field->maxlength.' caratteri');
        } elseif ($field->minlength && (strlen($value) < $field->minlength)) {
            $this->addFieldError('minlength', $field, $field->minlength.' caratteri');
        } elseif ($field->fixlength && !in_array(strlen($value),$field->fixlength)) {
            $this->addFieldError('fixlength', $field, implode(' o ',$field->fixlength).' caratteri');
        }
    }

    protected function validateFloatValue(ModelField $field, $value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
            $this->addFieldError('numeric', $field);
        }
    }

    protected function validateIntegerValue(ModelField $field, $value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
            $this->addFieldError('integer', $field);
        }
    }

    protected function validateEmailAddressValue(ModelField $field, $value)
    {
        if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            $this->addFieldError('email', $field);
        }
    }

    private function grabUploadedFile(&$field)
    {
        if (!is_array($_FILES) || !array_key_exists($field->html, $_FILES) || empty($_FILES[$field->html]['name'])) {
            $field->readonly = true;
            return $field->value;
        }
        $upload = new UploadManager();
        try {
            $field->value = $upload->saveFile($field->html, $field->uploadDir);
            $field->readonly = false;
        } catch(\Exception $e) {
            $this->addError($e->getMessage());
            $field->readonly = true;
        }
        $this->actions['after-update'] = 'refresh';
        $this->afterUpload($field->value, $field);
        return $field->value;
    }

    public function setValue($fieldName, $value, $defaultValue = null)
    {
        $this->field[$fieldName]->setValue($value, $defaultValue);
    }

    public function softDelete($field, $value)
    {
        $this->softdelete = array($field => $value);
    }

    public function getValue($key)
    {
        return is_array($this->values) && array_key_exists($key, $this->values) ? $this->values[$key] : null;
    }

    protected function raiseException($errorMessage, $errorNum = 500)
    {
        throw new ModelException($errorMessage, $errorNum);
    }

    protected function beforeExec()
    {
    }

    protected function beforeInsert()
    {
    }

    protected function beforeUpdate()
    {
    }

    protected function beforeDelete()
    {
    }

    protected function afterExec()
    {
    }

    protected function afterInsert($id)
    {
    }

    protected function afterUpdate()
    {
    }

    protected function afterDelete()
    {
    }

    protected function afterUpload($filename, $field = null)
    {
    }
}
