<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Position;
use App\Models\Service;
use App\Models\Staff;

class StaffController extends Controller {
    public function staff() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        $services = Service::whereHas('service_type', function($q) {
            $q->whereNotIn('name', ['Temazcal']);
        })->where('status', 1)->get();
        return Inertia::render('admin/Staff', [
            'module'    => $module,
            'menu'      => Modules::modulesMenu(),
            'positions' => Position::where('status', 1)->get(),
            'services'  => $services
        ]);
    }

    public function getStaff(Request $request) {
        try {
            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset
            $search     = $request->search;
            $order      = $request->order;

            $query  = Staff::with(['position', 'schedules', 'services']);
            if ($search['position_id']) $query->where('position_id', $search['position_id']);
            
            if ($search['name']) $query->whereRaw('name LIKE "%'.$search['name'].'%"');
            
            if ($search['first_name']) $query->whereRaw('first_name LIKE "%'.$search['first_name'].'%"');
            
            if ($search['last_name']) $query->whereRaw('last_name LIKE "%'.$search['last_name'].'%"');
            
            if ($search['email']) $query->whereRaw('email LIKE "%'.$search['email'].'%"');
            
            if ($search['phone']) $query->whereRaw('phone LIKE "%'.$search['phone'].'%"');
            
            if ($search['status'] !== 'all') $query->where('status', $search['status']);
            
            $staff     = $query->offset($offset)->limit($limit)->orderBy($order['orderBy'], $order['order'])->get();
            $totalRows = $query->count();
            return Response::response(null, ['staff' => $staff, 'totalRows' => $totalRows]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveStaff(Request $request) {
        try {
            Staff::create([
                'position_id' => $request->position_id,
                'name'        => $request->name,
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'birthdate'   => $request->birthdate,
                'curp'        => $request->curp,
                'rfc'         => $request->rfc,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'commission'  => $request->commission,
                'created_by'  => auth()->user()->id
                // 'image_profile' => $request->image_profile,
            ]);
            return Response::response('El staff se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editStaff(Request $request) {
        try {
            $txt                = 'modificó';
            $staff              = Staff::find($request->id);
            $staff->position_id = $request->position_id;
            $staff->name        = $request->name;
            $staff->first_name  = $request->first_name;
            $staff->last_name   = $request->last_name;
            $staff->birthdate   = $request->birthdate;
            $staff->curp        = $request->curp;
            $staff->rfc         = $request->rfc;
            $staff->email       = $request->email;
            $staff->phone       = $request->phone;
            $staff->commission  = $request->commission;
            if ($request->status === 0 || $request->status === 1) {
                $staff->status = $request->status;
                $txt           = $request->status === 1 ? 'activo' : 'desactivo';
            }
            $staff->updated_by = auth()->user()->id;
            $staff->save();
            return Response::response('El staff se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteStaff($id) {
        try {
            $staff             = Staff::find($id);
            $staff->deleted_by = auth()->user()->id;
            $staff->save();
            $staff->delete();
            return Response::response('El staff se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function staffAndFile(Request $request) {
        if ($request->id === 'null') {
            self::saveStaff($request);
        } else {
            self::editStaff($request);
        }
    }
}
