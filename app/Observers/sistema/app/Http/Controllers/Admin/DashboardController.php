<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Repositories\Bundle\BundleRepository;
use App\Services\ChartService;
use App\Services\Report\ReportService;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;
use Lava;
use App\Support\SiteSettings;

class DashboardController extends Controller
{
    /**
     * Display a admin dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (auth()->user()->isSuperUser()) {

            return app()->make(ReportController::class)->charts($request, "admin.pages.dashboard.superuser");

        } elseif (auth()->user()->isAffiliate()) {

            return app()->make(ReportController::class)->userCommissions($request, "admin.pages.dashboard.affiliate");

        }

        return app()->make(ReportController::class)->userMetrics($request, "admin.pages.dashboard.seller");
    }
}
