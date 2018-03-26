<?php

namespace App\Domain\FormFields;

class CheckboxField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "checkbox";
    }
}