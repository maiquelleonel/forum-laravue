<?php

namespace App\Domain\FormFields;

class MonetaryField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "monetary";
    }
}