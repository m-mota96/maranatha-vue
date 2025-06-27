<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Module;

class ModuleController extends Controller {
    public function modules() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Module', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }

    public function getModules(Request $request) {
        try {
            $modules = Module::with(['dad'])->get();
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
