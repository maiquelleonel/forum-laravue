<?php

namespace App\Domain\FormFields;

abstract class Field
{
    private $name;
    /**
     * @var array
     */
    private $attributes;

    /**
     * Field constructor.
     * @param $name
     * @param array $attributes
     */
    public function __construct($name, $attributes=[])
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return isset($this->attributes["value"]) ? $this->attributes["value"] : null;
    }

    /**
     * @return string
     */
    abstract public function getType();
}