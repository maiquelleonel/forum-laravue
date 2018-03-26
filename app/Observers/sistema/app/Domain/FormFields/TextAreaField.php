<?php

namespace App\Domain\FormFields;

class TextAreaField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "textarea";
    }
}