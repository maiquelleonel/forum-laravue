<?php

namespace App\Http\Controllers\LandingPages;

use App\Http\Controllers\Controller;

class LPGuiaSobrevivenciaController extends Controller
{
    public function getIndex()
    {
        return view("landing-pages.guia-sobrevivencia.index");
    }
}