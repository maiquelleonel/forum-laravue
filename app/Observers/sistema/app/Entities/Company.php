<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = "company";

    protected $fillable = [
        "name",
        "phone",
        "cnpj",
        "email",
        "logo"
    ];

}
