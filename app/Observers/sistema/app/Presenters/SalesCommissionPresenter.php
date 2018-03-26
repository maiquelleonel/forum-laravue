<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

class SalesCommissionPresenter extends Presenter
{
    public function status()
    {
        $status = $this->entity->status;

        return "<label class='label label-" . config("status.$status.label") . "'>" .
                    \Html::faIcon( config("status.$status.icon") ) . " | " .
                    trans( mb_strtolower("status.$status") ) .
               "</label>";
    }
}