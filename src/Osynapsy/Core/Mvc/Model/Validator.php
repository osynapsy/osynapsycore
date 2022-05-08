<?php
namespace Osynapsy\Core\Mvc\Model\Record\Action;

use Osynapsy\Mvc\Model\Field;

/**
 * Description of ModelFieldCheck
 *
 * @author pietr
 */
class Validator
{
    const ERROR_NOT_EMAIL = 'Il campo <fieldname> non contiene un indirizzo mail valido.';
    const ERROR_NOT_NULL = 'Il campo <fieldname> è obbligatorio.';
    const ERROR_NOT_NUMERIC = 'Il campo <fieldname> accetta solo valori numerici.';
    const ERROR_NOT_INTEGER = 'Il campo <fieldname> accetta solo numeri interi.';
    const ERROR_NOT_UNIQUE = '<value> è già  presente in archivio.';
    const ERROR_LENGTH_EXCEEDS = 'Il campo <fieldname> deve avere una lunghezza massima di %s caratteri';
    const ERROR_LENGTH_MIN = 'Il campo <fieldname> deve avere una lunghezza minima di %s caratteri';
    const ERROR_LENGTH_FIX = 'Il campo <fieldname> accetta solo valori con una lunghezza pari a %s caratteri';

    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function isNotNull($field)
    {
        $value = $field->value;
        if (!$field->isNullable() && $value !== '0' && empty($value)) {
            throw new \Exception(self::ERROR_NOT_NULL);
        }
    }

    public function isUnique($field)
    {
        $value = $field->value;
        if (!$field->isUnique() || empty($value)) {
            return;
        }
        $table = $this->getModel()->getRecord()->table();
        $numberOfOccurences = $this->getModel()->getDb()->findOne(
            sprintf("SELECT COUNT(*) FROM %s WHERE %s = ?", $table, $field->name),
            [$value]
        );
        if (!empty($numberOfOccurences)) {
            throw new \Exception(self::ERROR_NOT_UNIQUE);
        }
    }

    public function isEmail($value)
    {
        if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new \Exception(self::ERROR_NOT_EMAIL);
        }
    }

    public function isFloat($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
            throw new \Exception(self::ERROR_NOT_NUMERIC);
        }
    }

    public function isInteger($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
            throw new \Exception(self::ERROR_NOT_INTEGER);
        }
    }

    public function validateCharLength($field)
    {
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($field->value) > $field->maxlength)) {
            throw new \Exception(sprintf(self::ERROR_LENGTH_EXCEEDS, $field->maxlength));
        }
        if ($field->minlength && (strlen($field->value) < $field->minlength)) {
            throw new \Exception(sprintf(self::ERROR_LENGTH_MIN, $field->minlength));
        }
        if ($field->fixlength && !in_array(strlen($field->value), $field->fixlength)) {
            throw new \Exception(sprintf(self::ERROR_LENGTH_FIX, implode(' o ',$field->fixlength)));
        }
    }

    public function validateType(Field $field)
    {
        $value = $field->value;
        switch ($field->type) {
            case Field::TYPE_NUMBER:
                $this->isFloat($value);
                break;
            case Field::TYPE_INTEGER:
                $this->isInteger($value);
                break;
            case Field::TYPE_EMAIL:
                $this->isEmail($value);
                break;
        }
    }

    public function validate(Field $field)
    {
        $this->isNotNull($field);
        $this->validateCharLength($field);
        $this->isUnique($field);
        $this->validateType($field);
        $this->extraChecks();
    }

    public function extraChecks()
    {
    }
}
