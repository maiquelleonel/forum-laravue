<?php

namespace App\Http\Controllers\Admin;

use App\Support\SiteSettings;
use Artesaos\Defender\Permission;

class PermissionController extends CrudController
{
    /**
     * PermissionsController constructor.
     * @param SiteSettings $settings
     * @param Permission $permission
     */
    public function __construct(SiteSettings $settings, Permission $permission)
    {
        parent::__construct($settings, $permission);
    }
}
