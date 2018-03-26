<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ConfigCommissionGroup;
use App\Http\Requests;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class ConfigCommissionGroupController extends CrudController
{
    protected $deleteAction = true;

    protected $eagerLoading = ["rules.currency"];

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param ConfigCommissionGroup $model
     */
    public function __construct(SiteSettings $siteSettings, ConfigCommissionGroup $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function store(Request $request)
    {
        parent::store($request);
        return redirect()->action("\\".get_class( $this ) . "@edit", $this->lastInsertedModel->id);
    }

    public function destroy($id)
    {
        $model = $this->model->find( $id );

        if( $model->users->count() > 0 ){
            $links = "";

            foreach($model->users as $user){
                $links[] = \Html::link(route("admin:users.edit", $user->id), $user->name);
            }

            $message = implode(", ", $links);

            session()->flash(
                "error",
                "Não é possível excluir este grupo, os seguintes usuários estão vinculados neles:<br>" . $message
            );

            return back();
        }

        return parent::destroy($id);
    }

    public function getColumns()
    {
        return [
            "name",
            "Usuários no grupo" => function($model){
                return $model->users->count();
            }
        ];
    }
}
