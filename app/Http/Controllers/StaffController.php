<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\AppointmentServiceStaff;
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
            'positions' => Position::where('status', 1)->orderBy('name')->get(),
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

            $query = Staff::with(['position', 'schedules', 'services']);

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
            $request->validate([
                'file' => 'nullable|file|mimes:jpg,jpeg,png|max:1024', // tamaño en kilobytes (1024 KB = 1 MB)
            ], [
                'file.mimes' => 'Por favor elige una imagen en formato jpg, jpeg o png.',
                'file.max'   => 'La imagen debe mesar 1MB o menos.'
            ]);

            $reqStaff    = json_decode($request->staff, true);
            $reqServices = json_decode($request->services, true);
            $services    = [];
            foreach ($reqServices as $s) {
                if ($s['val']) $services[] = $s['id'];
            }

            $fileName = null;
            if ($request->file) {
                $file            = $request->file;
                $extension       = $file->getClientOriginalExtension();
                $fileName        = uniqid().'.'.$extension;
                $destinationPath = public_path('staff');
                // Crear carpeta si no existe
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                // Guardar el archivo
                $file->move($destinationPath, $fileName);
            }

            if ($reqStaff['id'] === null) {
                $txt   = 'guardo';
                $staff = Staff::create([
                    'position_id'   => $reqStaff['position_id'],
                    'name'          => $reqStaff['name'],
                    'first_name'    => $reqStaff['first_name'],
                    'last_name'     => $reqStaff['last_name'],
                    'birthdate'     => $reqStaff['birthdate'] ? $reqStaff['birthdate'] : null,
                    'curp'          => $reqStaff['curp'],
                    'rfc'           => $reqStaff['rfc'],
                    'email'         => $reqStaff['email'],
                    'phone'         => $reqStaff['phone'],
                    'commission'    => $reqStaff['commission'],
                    'image_profile' => $fileName,
                    'created_by'  => auth()->user()->id
                ]);
            } else {
                $txt                  = 'modificó';
                $staff                = Staff::find($reqStaff['id']);
                $staff->position_id   = $reqStaff['position_id'];
                $staff->name          = $reqStaff['name'];
                $staff->first_name    = $reqStaff['first_name'];
                $staff->last_name     = $reqStaff['last_name'];
                $staff->birthdate     = $reqStaff['birthdate'] ? $reqStaff['birthdate'] : null;
                $staff->curp          = $reqStaff['curp'];
                $staff->rfc           = $reqStaff['rfc'];
                $staff->email         = $reqStaff['email'];
                $staff->phone         = $reqStaff['phone'];
                $staff->commission    = $reqStaff['commission'];
                $staff->image_profile = $fileName;
                $staff->updated_by    = auth()->user()->id;
                $staff->save();
            }
            $staff->services()->sync($services);
            return Response::response('El staff se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response($th->getMessage(), null, true, 500);
        }
    }

    public function editStaff(Request $request) {
        try {
            $txt               = $request->status === 1 ? 'activo' : 'desactivo';
            $staff             = Staff::find($request->id);
            $staff->status     = $request->status;
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

    public function searchStaff(Request $request) {
        try {
            $weekDay  = date("w", strtotime($request->date)) + 1;
            $datetime = \DateTime::createFromFormat('h:i A', $request->horary);
            $horary   = $datetime->format('H:i');
            $staff    = Staff::with([
                'appointments.services',
                'services:id,name',
                'schedules:id,staff_id,day,start_time,meal_start_time,meal_end_time,end_time',
            ])
            ->select('id', 'name', 'first_name', 'last_name')
            ->whereHas('schedules', function($q) use($weekDay, $horary) {
                $q->where('day', $weekDay)->where('start_time', '<=', $horary)->where('end_time', '>=', $horary)->where('status', 1);
            })
            ->orderBy('name')
            ->get();
            
            $servicesForStaff = AppointmentServiceStaff::with([
                'service:id,name,color',
                'staff:id,name,first_name,last_name'
            ])->whereHas('appointment', function($q) use($request) {
                $q->where('date', $request->date);
            })->get();
            
            return Response::response(null, ['staff' => $staff, 'servicesForStaff' => $servicesForStaff]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
