<?php
namespace Osynapsy\Core\Mvc\Model;

use Osynapsy\Mvc\Model\Field;

/**
 * Description of ModelErrorException
 *
 * @author Pietro
 */
class ModelErrorException extends \Exception
{
    private $errors = [];

    public function setError($message)
    {
        $this->errors[] = $message;
        $this->appendToMessage($message);
    }

    public function setErrorOnField(Field $field, $rawErrorMessage)
    {
        $errorMessage = str_replace(
            ['<fieldname>', '<value>'],
            ['<!--'.$field->html.'-->', $field->value],
            $rawErrorMessage
        );
        $this->errors[$field->html] = $errorMessage;
        $this->appendToMessage($errorMessage);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function appendToMessage($message)
    {
        $this->message .= PHP_EOL.$message;
    }
}
