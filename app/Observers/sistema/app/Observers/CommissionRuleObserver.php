<?php

namespace App\Observers;


use App\Entities\ConfigCommissionRule;
use App\Entities\ShavingCounter;

class CommissionRuleObserver
{
    public function updating(ConfigCommissionRule $rule)
    {
        if($rule->isDirty("shaving_rate") OR $rule->isDirty("value") OR $rule->isDirty("type") OR $rule->isDirty("currency_id")){
            $this->resetShavingCounter($rule);
        }
    }

    private function resetShavingCounter(ConfigCommissionRule $rule)
    {
        $sites      = $rule->sites->lists("id");

        $users  = $rule->users->lists("id");

        ShavingCounter::query()
                        ->whereIn("site_id", $sites)
                        ->whereIn("user_id", $users)
                        ->delete();
    }
}