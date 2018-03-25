<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateFormat
{

    public function getCreatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    private function formatDate($value)
    {

        if ($format = env('DATE_FORMAT')) {
            return Carbon::createFromFormat($format, $value)->format('d/m/Y H:i');
        }
        return $value;
    }
}
