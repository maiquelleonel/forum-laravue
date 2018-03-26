<?php

namespace App\Domain\FormFields;

class TelephoneField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "tel";
    }
}