<?php

namespace App\Http\Controllers\Admin;

use App\Entities\CampaignCost;
use App\Entities\PageVisit;
use App\Http\Requests\Admin\CampaignCostRequest;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;

class CampaignCostsController extends Controller
{
    public function index(Request $request)
    {
        $months = trans("payments.creditcard.months");
        $years  = range( date("Y")-3, date("Y")+3);
        $years  = array_combine($years, $years);

        $medias = PageVisit::getMedias();
        $medias = $medias->combine($medias);

        $month = $request->get("month", date("m"));
        $year  = $request->get("year", date("Y"));
        $media = $request->get("utm_content", $medias->first());

        $campaigns = $media ? PageVisit::getCampaigns($media) : collect([]);

        $costs = CampaignCost::whereMonth("cost_day", "=", $month)
                                ->whereYear("cost_day", "=", $year)
                                ->whereIn("utm_campaign", $campaigns)
                                ->get();

        return view("admin.pages.billing.campaign-costs", compact(
            "months", "years", "medias", "campaigns", "costs", "month", "year", "media"
        ));
    }

    public function store(CampaignCostRequest $request)
    {
        list($year, $month, $day) = array_values($request->only("year", "month", "day"));
        $date = Carbon::create($year, $month, $day);

        $cost = CampaignCost::firstOrCreate([
            "cost_day"      => $date->toDateString(),
            "utm_campaign"  => $request->get("utm_campaign")
        ]);

        $cost->update([
            "cost"  => $request->get("cost")
        ]);

        return response()->json( $cost );
    }
}
