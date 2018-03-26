<?php

namespace App\Domain\FormFields;

class ImageUploadField extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return "imageUpload";
    }
}