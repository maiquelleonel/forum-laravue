<?php

namespace App\Domain\FormFields;

class LabelField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "label";
    }
}