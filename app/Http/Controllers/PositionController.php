<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Position;

class PositionController extends Controller {
    public function positions() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Position', [
            'module'    => $module,
            'menu'      => Modules::modulesMenu(),
        ]);
    }

    public function getPositions(Request $request) {
        try {
            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset
            $search     = $request->search;
            $order      = $request->order;

            $query = Position::whereNotNull('id');
            
            if ($search['name']) $query->whereRaw('name LIKE "%'.$search['name'].'%"');

            if ($search['status'] !== 'all') $query->where('status', $search['status']);
            
            $positions = $query->offset($offset)->limit($limit)->orderBy($order['orderBy'], $order['order'])->get();
            $totalRows = $query->count();
            return Response::response(null, ['positions' => $positions, 'totalRows' => $totalRows]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function savePosition(Request $request) {
        try {
            Position::create([
                'name' => $request->name,
            ]);
            return Response::response('El puesto se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editPosition(Request $request) {
        try {
            $txt            = 'modificó';
            $position       = Position::find($request->id);
            $position->name = $request->name;
            if ($request->status === 0 || $request->status === 1) {
                $position->status = $request->status;
                $txt              = $request->status === 1 ? 'activo' : 'desactivo';
            }
            $position->save();
            return Response::response('El puesto se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deletePosition($id) {
        try {
            $position = Position::find($id);
            $position->delete();
            return Response::response('El puesto se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
