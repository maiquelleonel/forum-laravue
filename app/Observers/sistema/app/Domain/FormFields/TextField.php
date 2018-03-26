<?php

namespace App\Domain\FormFields;

class TextField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "text";
    }
}