<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Inventory;
use App\Models\ProductType;
use App\Models\Reference;

class InventoryController extends Controller {
    public function inventories() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Inventory', [
            'module'      => $module,
            'menu'        => Modules::modulesMenu(),
            'references'  => Reference::where('id', '<>', 3)->orderBy('name')->get(),
            'productType' => ProductType::where('status', true)->get()
        ]);
    }

    public function getInventory(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at', 'quantity', 'product_cost', 'expiration_date'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Inventory::with([
                'product:id,product_type_id,name,content,abreviation,brand,type_sale',
                'reference',
                'provider:id,name,seller',
                'product.productType:id,type'
            ])->whereNotIn('reference_id', [3]);
            
            if (!empty($search['product_name'])) {
                $query->whereHas('product', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['product_name'].'%');
                });
            }

            if (!empty($search['type'])) $query->where('type', $search['type']);
            
            if (!empty($search['reference_id'])) $query->where('reference_id', $search['reference_id']);

            if (!empty($search['product_cost'])) $query->whereLike('product_cost', '%'.$search['product_cost'].'%');

            if (!empty($search['dates'])) $query->whereBetween('created_at', [$search['dates'][0], $search['dates'][1]]);

            if (isset($search['productType'])) {
                $query->whereHas('product', function($q) use($search) {
                    $q->whereIn('product_type_id', $search['productType']);
                });
            }
            
            $inventories = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            return Response::response(null, ['inventories' => $inventories->items(), 'totalRows' => $inventories->total()]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveInventory(Request $request) {
        try {
            Inventory::create([
                'product_id'      => $request->product_id,
                'reference_id'    => $request->reference_id,
                'provider_id'     => ($request->provider_id && $request->provider) ? $request->provider_id : null,
                'type'            => $request->type,
                'quantity'        => $request->quantity,
                'expiration_date' => $request->expiration_date,
                'batch'           => $request->batch,
                'product_cost'    => ($request->reference_id === 1 && $request->type === 'input') ? $request->product_cost : null,
                'description'     => $request->description,
                'created_by'      => auth()->user()->id,
            ]);
            return Response::response('El registro de guardó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteInventory($id) {
        try {
            $inventory = Inventory::find($id);
            $inventory->deleted_by = auth()->user()->id;
            $inventory->save();
            $inventory->delete();
            return Response::response('El registro de eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
