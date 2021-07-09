<?php

namespace api\modules\v1\pojo;

class ResponseObject
{
    /**
     * ResponseObject constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $key => $value){
            $this->__set($key, $value);
        }
    }

    public function __get($name)
    {
        if(property_exists($this, $name)){
            return $this->{$name};
        }
        throw new \InvalidArgumentException('Fetching unknown value ' . $name);
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}