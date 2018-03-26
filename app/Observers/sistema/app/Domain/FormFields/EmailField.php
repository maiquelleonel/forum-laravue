<?php

namespace App\Domain\FormFields;

class EmailField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "email";
    }
}