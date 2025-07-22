<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Customer;

class CustomerController extends Controller {
    public function customers() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Customer', [
            'module'    => $module,
            'menu'      => Modules::modulesMenu(),
        ]);
    }

    public function getCustomers(Request $request) {
        try {
            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset
            $search     = $request->search;
            $order      = $request->order;

            $query = Customer::whereNotNull('id');
            
            if ($search['name']) $query->whereRaw('name LIKE "%'.$search['name'].'%"');
            
            if ($search['email']) $query->whereRaw('email LIKE "%'.$search['email'].'%"');

            if ($search['phone']) $query->whereRaw('phone LIKE "%'.$search['phone'].'%"');

            if ($search['status'] !== 'all') $query->where('status', $search['status']);
            
            $customers = $query->offset($offset)->limit($limit)->orderBy($order['orderBy'], $order['order'])->get();
            $totalRows = $query->count();
            return Response::response(null, ['customers' => $customers, 'totalRows' => $totalRows]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveCustomer(Request $request) {
        try {
            Customer::create([
                'name'         => $request->name,
                'birthdate'    => $request->birthdate,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'company_name' => $request->company_name,
                'rfc'          => $request->rfc,
                'address'      => $request->address,
                'created_by'   => auth()->user()->id
            ]);
            return Response::response('El cliente se guardo correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editCustomer(Request $request) {
        try {
            $txt                    = 'modificó';
            $customer               = Customer::find($request->id);
            $customer->name         = $request->name;
            $customer->birthdate    = $request->birthdate;
            $customer->email        = $request->email;
            $customer->phone        = $request->phone;
            $customer->company_name = $request->company_name;
            $customer->rfc          = $request->rfc;
            $customer->address      = $request->address;
            $customer->updated_by   = auth()->user()->id;
            if ($request->status === 0 || $request->status === 1) {
                $customer->status = $request->status;
                $txt              = $request->status === 1 ? 'activo' : 'desactivo';
            }
            $customer->save();
            return Response::response('El cliente se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteCustomer($id) {
        try {
            $customer             = Customer::find($id);
            $customer->deleted_by = auth()->user()->id;
            $customer->save();
            $customer->delete();
            return Response::response('El cliente se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
