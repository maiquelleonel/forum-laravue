<?php

namespace App\Http\Controllers\LandingPages;

use App\Http\Controllers\Controller;

class LPDicasSobrevivenciaController extends Controller
{
    public function getIndex()
    {
        return view("landing-pages.dicas-sobrevivencia.index");
    }
}