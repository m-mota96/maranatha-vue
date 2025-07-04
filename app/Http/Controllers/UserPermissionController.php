<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\User;

class UserPermissionController extends Controller {
    public function usersPermissions() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/UserPermission', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }

    public function permissionUser(Request $request) {
        try {
            $permissions = $request->modules;
            array_shift($permissions);
            $permissions = array_values($permissions);
            $modules     = [];
            foreach ($permissions as $key => $p) {
                if ($p['active']) $modules[] = $p['id'];
            }
            $user = User::find($request->user_id);
            $user->modules()->sync($modules);
            return Response::response('Los permisos se actualizaron correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
