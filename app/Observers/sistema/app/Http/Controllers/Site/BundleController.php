<?php

namespace App\Http\Controllers\Site;

use App\Entities\Bundle;

class BundleController extends BaseController
{
    /**
     * Select bundle to checkout
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function select()
    {
        if( $this->settings->isRemarketing() ) {
            $query = Bundle::onlyRemarketing();
        } else {
            $query = Bundle::onlyDefault();
        }
        $bundles  = $query->where('bundle_group_id', $this->settings->getSite()->bundle_group_id)
                          ->get()
                          ->sortByDesc('price');;

        return view('checkout::pages.bundles', compact('bundles'));
    }
}