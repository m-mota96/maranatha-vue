<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;

class ModulePermissionController extends Controller {
    public function modulesPermissions() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/ModulePermission', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }
}
