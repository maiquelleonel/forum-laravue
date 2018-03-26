<?php

namespace App\Services\Encoder;

use Doctrine\Common\Collections\ArrayCollection;

class JsonEncoder
{
    /**
     * @param object $object
     * @return string
     */
    public function encode($object){
        return json_encode($this->getFields($object));
    }

    /**
     * @param \stdClass
     * @return array
     */
    private function getFields($classObj){
        $fields = array();
        $reflect = new \ReflectionClass($classObj);
        $props   = $reflect->getProperties();
        if ($props) {
            foreach($props as $property){
                $property->setAccessible(true);
                $obj = $property->getValue($classObj);
                $name = $property->getName();
                $this->doProperty($fields, $name, $obj);
            }
        } else {
            $fields = $classObj;
        }

        return $fields;

    }

    /**
     * @param array $fields
     * @param string $name
     * @param mixed $obj
     */
    private function doProperty(&$fields, $name, $obj){
        if (is_array($obj)){
            $arrayFields = Array();
            foreach ($obj as $item){
                $key = key($obj);
                $this->doProperty($arrayFields, $key, $item);
                next($obj);
            }
            $fields[$name] = $arrayFields;
        }
        else if ($obj instanceof ArrayCollection){
            $arrayFields = Array();
            foreach ($obj as $item){
                $this->doProperty($arrayFields, $name, $item);
                next($obj);
            }
            $fields[$name] = $arrayFields;
        }
        else if (is_object($obj)){

            $fields[$name] = $this->getFields($obj);
            return;
        }
        else {
            $fields[$name] = $obj;
        }
    }
}