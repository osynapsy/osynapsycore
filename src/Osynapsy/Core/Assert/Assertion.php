<?php
namespace Osynapsy\Core\Assert;

class Assertion
{
    public static function between($value, $lowerLimit, $upperLimit, $message)
    {
        if ($lowerLimit > $value || $value > $upperLimit) {
            self::raiseException($message);
        }
        return true;
    }

    public static function digit($value, $message)
	{
		if (!\ctype_digit($value)){
			self::raiseException($message);
		}
		return true;
	}

    public static function equal($value1, $value2, $message)
    {
        if ($value1 != $value2) {
            self::raiseException($message);
        }
        return true;
    }

    public static function inArray($value, array $values, $message)
    {
        if (!in_array($value, $values)) {
            self::raiseException($message);
        }
        return true;
    }

    public static function integer($value, $message)
    {
        if (!is_int($value)) {
            self::raiseException($message);
        }
        return true;
    }

    public static function isAssoc(array $array, $message)
    {
        if (array() === $array) {
            $this->raiseException($message);
        }
        if (array_keys($array) !== range(0, count($array) - 1)) {
            $this->raiseException($message);
        }
    }

    public static function isEmpty($value, $message)
    {
        if (!empty($value)) {
            self::raiseException($message);
        }
        return true;
    }

    public static function isFalse($value, $message)
    {
        if ($value !== false) {
            self::raiseException($message);
        }
        return true;
    }

    public static function isTrue($value, $message)
    {
        if ($value !== true) {
            self::raiseException($message);
        }
        return true;
    }

    public static function isValidEmailAddress($value, $message)
    {
        if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            self::raiseException($message);
        }
        return true;
    }

    public static function greaterThan($value, $limit, $message)
    {
        if ($value <= $limit) {
            self::raiseException($message);
        }
        return true;
    }

    public static function greaterOrEqualThan($value, $limit, $message)
    {
        if ($value < $limit) {
            self::raiseException($message);
        }
        return true;
    }

    public static function lessThan($value, $limit, $message)
    {
        if ($value >= $limit) {
            self::raiseException($message);
        }
        return true;
    }

    public static function lessOrEqualThan($value, $limit, $message)
    {
        if ($value > $limit) {
            self::raiseException($message);
        }
        return true;
    }

    public static function notEmpty($value, $message)
    {
        if (empty($value)) {
            self::raiseException($message);
        }
        return true;
    }

    protected static function raiseException($message, $code = null)
    {
        throw new AssertException($message, $code);
    }
}
