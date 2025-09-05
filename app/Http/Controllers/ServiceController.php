<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Service;
use App\Models\ServiceType;

class ServiceController extends Controller {
    public function services() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Service', [
            'module'      => $module,
            'menu'        => Modules::modulesMenu(),
            'serviceType' => ServiceType::orderBy('name')->get()
        ]);
    }

    public function getServices(Request $request) {
        try {
            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset
            $search     = $request->search;
            $order      = $request->order;

            $query  = Service::with(['service_type']);
            if ($search['service_type_id']) $query->where('service_type_id', $search['service_type_id']);

            if ($search['name']) $query->whereRaw('name LIKE "%'.$search['name'].'%"');

            if ($search['price']) $query->whereRaw('price LIKE "%'.$search['price'].'%"');
            
            if ($search['discounted_price']) $query->whereRaw('discounted_price LIKE "%'.$search['discounted_price'].'%"');
            
            if ($search['status'] !== 'all') $query->where('status', $search['status']);
            
            $services  = $query->offset($offset)->limit($limit)->orderBy($order['orderBy'], $order['order'])->get();
            $totalRows = $query->count();
            return Response::response(null, ['services' => $services, 'totalRows' => $totalRows]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveService(Request $request) {
        try {
            Service::create([
                'service_type_id'  => $request->service_type_id,
                'name'             => $request->name,
                'price'            => $request->price,
                'discounted_price' => $request->discounted_price,
                'time'             => $request->time,
                'color'            => $request->color,
                'created_by'       => auth()->user()->id
            ]);
            return Response::response('El servicio se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editService(Request $request) {
        try {
            $txt                       = 'modificó';
            $service                   = Service::find($request->id);
            $service->service_type_id  = $request->service_type_id;
            $service->name             = $request->name;
            $service->price            = $request->price;
            $service->discounted_price = $request->discounted_price;
            $service->time             = $request->time;
            $service->color            = $request->color;
            if ($request->status === 0 || $request->status === 1) {
                $service->status = $request->status;
                $txt             = $request->status === 1 ? 'activo' : 'desactivo';
            }
            $service->updated_by = auth()->user()->id;
            $service->save();
            return Response::response('El servicio se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteService($id) {
        try {
            $service             = Service::find($id);
            $service->deleted_by = auth()->user()->id;
            $service->save();
            $service->delete();
            return Response::response('El servicio se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
