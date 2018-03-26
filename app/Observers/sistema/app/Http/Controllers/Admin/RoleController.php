<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\CheckboxGroupField;
use App\Domain\FormFields\TextField;
use App\Support\SiteSettings;
use Artesaos\Defender\Permission;
use Artesaos\Defender\Role;
use App\Http\Requests\Admin\Request;

class RoleController extends CrudController
{
    /**
     * PermissionsController constructor.
     * @param SiteSettings $settings
     * @param Role $role
     */
    public function __construct(SiteSettings $settings, Role $role)
    {
        parent::__construct($settings, $role);
    }

    /**
     * @inheritdoc
     */
    public function update(Request $request, $id)
    {
        $response = parent::update($request, $id);

        $this->lastUpdatedModel->syncPermissions( $request->get("permissions", []) );

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function store(Request $request)
    {
        $response = parent::store($request);

        $this->lastInsertedModel->syncPermissions( $request->get("permissions", []) );

        return $response;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $permissions = Permission::all();
        $subGroups  = [];

        foreach($permissions as $permission){
            $subGroupName = str_ireplace("admin:", "", $permission->name);
            $subGroupName = explode(".", $subGroupName)[0];
            $subGroupName = trans("validation.attributes.{$subGroupName}");
            $subGroups[$subGroupName][$permission->id] = $permission->readable_name;
        }

        ksort($subGroups);

        $fields[] = new TextField("name");

        foreach ( $subGroups as $name => $permissions ) {
            $fields[] = new CheckboxGroupField("permissions", $permissions, [
                'data-split'=> 4,
                'label'     => mb_strtoupper($name)
            ]);
        }

        return [$fields];
    }
}
