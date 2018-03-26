<?php

namespace App\Domain\FormFields;

class RadioField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "radio";
    }
}