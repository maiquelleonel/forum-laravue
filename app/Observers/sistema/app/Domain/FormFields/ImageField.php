<?php

namespace App\Domain\FormFields;

class ImageField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "image";
    }
}