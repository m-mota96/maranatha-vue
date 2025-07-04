<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
            $modules = Modules::allModules();
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function parentModules(Request $request) {
        try {
            $modules = Modules::modulesNewMenu();
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function modulesMenu(Request $request) {
        try {
            $modules = Modules::modulesMenu();
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function allModulesMenu(Request $request) {
        try {
            $modules = Modules::allModulesMenu();
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function userModules($userId) {
        try {
            $modules = Modules::userModules($userId);
            return Response::response(null, $modules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveMenu(Request $request) {
        try {
            // $module = Module::where('name', $request->name)->orWhere('target', $request->target)->first();
            // if ($module) {
            //     return Response::response(
            //         'Ya existe un módulo con el mismo nombre o url.<br>Por favor intenta con otro nombre o url.',
            //         null,
            //         true,
            //         409
            //     );
            // }
            Module::create([
                'module_id'   => $request->menu_id ? $request->menu_id : null,
                'name'        => $request->name,
                'icon'        => $request->icon,
                'target'      => $request->target,
                'class'       => $request->class,
                'description' => $request->description
            ]);
            return Response::response('El módulo se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editMenu(Request $request) {
        try {
            $txt                 = 'modificó';
            $module              = Module::find($request->id);
            $module->module_id   = $request->menu_id ? $request->menu_id : null;
            $module->name        = $request->name;
            $module->icon        = $request->icon;
            $module->target      = $request->target;
            $module->class       = $request->class;
            $module->description = $request->description;
            if ($request->status === 0 || $request->status === 1) {
                $module->status = $request->status;
                $txt            = $request->status === 1 ? 'activo' : 'desactivo';
            }
            $module->save();
            return Response::response('El módulo se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
