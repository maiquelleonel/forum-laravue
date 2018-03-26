<?php

namespace App\Domain\FormFields;

class CheckboxGroupField extends Field
{
    private $values;

    /**
     * SelectField constructor.
     * @param $name
     * @param array $values
     * @param array $attributes
     */
    public function __construct($name, $values = [null], $attributes = [])
    {
        $this->values = $values;
        parent::__construct($name, $attributes);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return "checkboxGroup";
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->values;
    }
}