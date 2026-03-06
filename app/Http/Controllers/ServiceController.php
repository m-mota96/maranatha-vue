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
            'menu'        => Modules::modulesMenu()
        ]);
    }

    public function getServices(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at', 'service_type_id', 'name', 'price', 'discounted_price', 'time'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Service::with(['service_type']);

            if (!empty($search['service_type_id'])) $query->where('service_type_id', $search['service_type_id']);

            if (!empty($search['name'])) $query->whereLike('name', '%'.$search['name'].'%');

            if (!empty($search['price'])) $query->whereLike('price', '%'.$search['price'].'%');
            
            if (!empty($search['discounted_price'])) $query->whereLike('discounted_price', '%'.$search['discounted_price'].'%');
            
            if (isset($search['status'])) $query->where('status', $search['status']);
            
            $services  = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            return Response::response(null, ['services' => $services->items(), 'totalRows' => $services->total()]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function getAllServices() {
        try {
            $services = Service::where('status', 1)->orderBy('name')->get();
            return Response::response(null, $services);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function getServiceTypes() {
        try {
            $serviceTypes = ServiceType::with(['services:id,service_type_id,name,price,discounted_price,time,require_staff,color'])
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($serviceType) {
                $serviceType->services = $serviceType->services->map(function ($service) {
                    $service->active   = false;
                    $service->quantity = 1;
                    return $service;
                });
                return $serviceType;
            });
            return Response::response(null, $serviceTypes);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveService(Request $request) {
        try {
            $serviceType = isset($request->serviceType) ? $request->serviceType : [];
            $service     = (object) $request->service;
            if (sizeof($serviceType) > 0) {
                foreach ($serviceType as $key => $s) {
                    ServiceType::create([
                        'name' => $s['name'],
                    ]);
                }
                $serviceType              = ServiceType::where('name', $service->service_type_name)->first();
                $service->service_type_id = $serviceType->id;
            }
            
            Service::create([
                'service_type_id'  => $service->service_type_id,
                'name'             => $service->name,
                'price'            => $service->price,
                'discounted_price' => $service->discounted_price,
                'time'             => $service->time,
                'color'            => $service->color,
                'require_staff'    => $service->require_staff,
                'created_by'       => auth()->user()->id
            ]);
            return Response::response('El servicio se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editService(Request $request) {
        try {
            $serviceType = isset($request->serviceType) ? $request->serviceType : [];
            $service     = (object) $request->service;
            if (sizeof($serviceType) > 0) {
                foreach ($serviceType as $key => $s) {
                    ServiceType::create([
                        'name' => $s['name'],
                    ]);
                }
                $serviceType              = ServiceType::where('name', $service->service_type_name)->first();
                $service->service_type_id = $serviceType->id;
            }

            $txt                       = 'modificó';
            $findService                   = Service::find($service->id);
            $findService->service_type_id  = $service->service_type_id;
            $findService->name             = $service->name;
            $findService->price            = $service->price;
            $findService->discounted_price = $service->discounted_price;
            $findService->time             = $service->time;
            $findService->color            = $service->color;
            $findService->require_staff    = $service->require_staff;
            if ($service->status === 0 || $service->status === 1) {
                $findService->status = $service->status;
                $txt                 = $service->status === 1 ? 'activo' : 'desactivo';
            }
            $findService->updated_by = auth()->user()->id;
            $findService->save();
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
