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

class Field
{
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_EMAIL = 'email';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_INTEGER = 'integer';
    const TYPE_NUMBER = 'numeric';
    const TYPE_STRING = 'string';

    private $repo = array(
        'existInForm' => true,
        'fixlength' => null,
        'is_pk' => false,
        'maxlength' => null,
        'minlength' => null,
        'nullable' => true,
        'readonly' => false,
        'rawvalue' => null,
        'unique' => false,
        'value' => null,
        'defaultValue' => null,
        'uploadDir' => '/upload'
    );
    public $value;
    private $model;
    public $type;

    public function __construct($model, $nameOnDb, $nameOnView, $type = 'string', $existInForm = true)
    {
        $this->model = $model;
        $this->name = $nameOnDb;
        $this->html = $nameOnView;
        $this->setType($type, $existInForm);
    }

    public function __get($key)
    {
        return array_key_exists($key,$this->repo) ? $this->repo[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->repo[$key] = $value;
    }

    public function __toString()
    {
        return implode(',', $this->repo);
    }

    public function existInForm()
    {
        return $this->existInForm;
    }

    public function isRequired($required = null)
    {
        if (is_null($required)) {
            return !$this->repo['nullable'];
        }
        $this->repo['nullable'] = !$required;
        return $this;
    }

    public function isNullable($v = null)
    {
        if (is_null($v)) {
            return $this->repo['nullable'];
        }
        $this->repo['nullable'] = $v;
        return $this;
    }

    public function isPkey($b = null)
    {
        if (is_null($b)) {
            return $this->is_pk;
        }
        $this->is_pk = $b;
        if ($this->value) {
            $html = $this->html;
            if (empty($_REQUEST[$html])) {
                $_REQUEST[$html] = $this->value;
            }
        }
        return $this;
    }

    public function isUnique($v = null)
    {
        if (is_null($v)) {
            return $this->repo['unique'];
        }
        $this->repo['unique'] = $v;
        return $this;
    }

    public function setFixLength($length)
    {
        if (!is_array($length)) {
            $length = array($length);
        }
        $this->fixlength = $length;
        return $this;
    }

    public function setMaxLength($length)
    {
        $this->maxlength = $length;
        return $this;
    }

    public function setMinLenght($length)
    {
        $this->minlength = $length;
        return $this;
    }

    public function setType($type, $existInForm = true)
    {
        $this->type = $type;
        $this->existInForm = in_array($type, ['file','image']) ? true : $existInForm;
        if (in_array($type, ['file','image'])) {
            $this->readonly = true;
        }
    }

    public function setValue($value, $default = null)
    {
        if ($value !== '0' && $value !== 0 && empty($value)) {
            $value = $default;
        }
        $this->value = $this->rawvalue = $value;
        $this->defaultValue = $default;
        if ($this->type === self::TYPE_DATE) {
            $this->adjustDateValue();
        }
        if ($this->type === self::TYPE_DATETIME) {
            $this->adjustDatetimeValue();
        }
        return $this;
    }

    private function adjustDateValue()
    {
        if (empty($this->value) || strpos($this->value, '/') === false) {
            return;
        }
        list($day, $month, $year) = explode('/', $this->value);
        $this->value = sprintf("%s-%s-%s", $year, $month, $day);
    }

    private function adjustDatetimeValue()
    {
        if (empty($this->value) || strpos($this->value, '/') === false) {
            return;
        }
        list($date, $time) = explode(' ', $this->value);
        list($day, $month, $year) = explode('/', $date);
        $this->value = sprintf("%s-%s-%s %s:00", $year, $month, $day, $time);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setUploadPath($path)
    {
        $this->uploadDir = $path;
        $this->setType(self::TYPE_FILE);
    }
}
