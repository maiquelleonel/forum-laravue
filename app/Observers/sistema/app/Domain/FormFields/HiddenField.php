<?php

namespace App\Domain\FormFields;

class HiddenField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "hidden";
    }
}