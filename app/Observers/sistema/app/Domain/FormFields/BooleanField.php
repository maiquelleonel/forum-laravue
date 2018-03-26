<?php

namespace App\Domain\FormFields;

class BooleanField extends Field
{
    private $values;

    /**
     * SelectField constructor.
     * @param $name
     * @param array $attributes
     */
    public function __construct($name, $attributes = [])
    {
        $this->values = [true => "Sim", false => "Não"];
        parent::__construct($name, $attributes);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return "select";
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->values;
    }
}