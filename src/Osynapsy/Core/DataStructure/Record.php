<?php
namespace Osynapsy\Core\DataStructure;

/**
 * Description of Record
 *
 * @author Pietro
 */
class Record
{
    protected $repo;

    public function __construct(array $data = [])
    {
        $this->repo = array_change_key_case($data, CASE_LOWER);
    }

    public function __call($name, $arguments)
    {
        switch(strtolower(substr($name, 0, 3))) {
            case 'get':
                return $this->__get(substr($name, 3));
            case 'set':
                return $this->__set(substr($name, 3), $arguments[0] ?? null);
        }
        throw new \Exception(sprintf('No method %s exists', $name));
    }

    public function __get($name)
    {
        return $this->repo[strtolower($name)];
    }

    public function __set($name, $value)
    {
        return $this->repo[strtolower($name)] = $value;
    }
}
