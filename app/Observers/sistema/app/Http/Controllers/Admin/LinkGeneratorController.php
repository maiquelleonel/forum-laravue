<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use Illuminate\Support\Debug\Dumper;

class LinkGeneratorController extends Controller
{
    public function create()
    {
        $sites = auth()->user()->offerSites;
        //dd($sites);
        return view("admin.pages.link-generator.create", compact("sites"));
    }

    public function store(Request $request)
    {
//
    }
}
