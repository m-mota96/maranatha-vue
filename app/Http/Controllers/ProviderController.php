<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Provider;

class ProviderController extends Controller {
    public function providers() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Provider', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }

    public function getProviders(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at', 'name', 'seller', 'phone', 'email'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Provider::query();
            
            if (!empty($search['name'])) $query->whereLike('name', '%'.$search['name'].'%');
            
            if (!empty($search['seller'])) $query->whereLike('seller', '%'.$search['seller'].'%');

            if (!empty($search['phone'])) $query->whereLike('phone', '%'.$search['phone'].'%');

            if (isset($search['email'])) $query->whereLike('email', '%'.$search['email'].'%');
            
            $providers = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            return Response::response(null, ['providers' => $providers->items(), 'totalRows' => $providers->total()]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function searchProvider(Request $request) {
        try {
            $providers = Provider::select('id', 'name', 'seller')
            ->whereLike('name', '%'.$request->name.'%')
            ->get();
            return Response::response(null, $providers);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveProvider(Request $request) {
        try {
            Provider::create([
                'name'       => $request->name,
                'seller'     => $request->seller,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'created_by' => auth()->user()->id,
            ]);
            return Response::response('El proveedor se guardó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editProvider(Request $request) {
        try {
            $provider             = Provider::find($request->id);
            $provider->name       = $request->name;
            $provider->seller     = $request->seller;
            $provider->email      = $request->email;
            $provider->phone      = $request->phone;
            $provider->address    = $request->address;
            $provider->updated_by = auth()->user()->id;
            $provider->save();
            return Response::response('El proveedor se modificó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteProvider($id) {
        try {
            $provider             = Provider::find($id);
            $provider->deleted_by = auth()->user()->id;
            $provider->save();
            $provider->delete();
            return Response::response('El proveedor se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
