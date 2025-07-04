<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\User;

class AdminUserController extends Controller {
    public function users() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/User', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }

    public function getUsers(Request $request) {
        try {
            $users = User::with(['modules'])
            // ->where('email', '<>', 'miguel.mota.murillo@gmail.com')
            ->get();
            return Response::response(null, $users);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveUser(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                return Response::response(
                    'El correo electrónico ya esta registrado.<br>Por favor intenta con otro.',
                    null,
                    true,
                    409
                );
            }
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('admin');
            return Response::response('El usuario se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editUser(Request $request) {
        try {
            $user = User::where('email', $request->email)->where('id', '<>', $request->id)->first();
            if ($user) {
                return Response::response(
                    'El correo electrónico ya esta registrado.<br>Por favor intenta con otro.',
                    null,
                    true,
                    409
                );
            }
            $txt         = 'modificó';
            $user        = User::find($request->id);
            $user->name  = $request->name;
            $user->email = $request->email;
            if ($request->password && $request->password_confirmation) {
                $user->password = $request->password;
            }
            if ($request->active === 0 || $request->active === 1) {
                $user->active = $request->active;
                $txt          = $request->active === 1 ? 'activo' : 'desactivo';
            }
            $user->save();
            return Response::response('El usuario se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
