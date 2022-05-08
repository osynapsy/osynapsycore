<?php
namespace Osynapsy\Db\Record;

/**
 * Description of InterfaceRecord
 *
 * @author pietr
 */
interface InterfaceRecord
{
    public function fieldExists($field);
    
    public function findByKey($key);
    
    public function findByAttributes(array $searchParameters);
            
    public function get($key = null);
    
    public function setValue($field, $value = null, $defaultValue = null);
}
