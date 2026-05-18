<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductType;

class ProductController extends Controller {
    public function products() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Product', [
            'module'      => $module,
            'menu'        => Modules::modulesMenu(),
            'productType' => ProductType::where('status', true)->get()
        ]);
    }

    public function getProducts(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at', 'available', 'name', 'price', 'discounted_price'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Product::with(['productType:id,type'])->select('*')
            ->selectRaw('
                (SELECT IFNULL(SUM(quantity), 0) FROM inventories WHERE product_id = products.id AND type = "input") - 
                (SELECT IFNULL(SUM(quantity), 0) FROM inventories 
                WHERE product_id = products.id 
                AND type = "output" 
                AND (reference_id != 3 OR (reference_id = 3 AND EXISTS (
                    SELECT 1 FROM sales WHERE sales.id = inventories.sale_id AND status_sale_id = 1
                )))
                ) as available
            ');
            
            if (!empty($search['name'])) $query->whereLike('name', '%'.$search['name'].'%');

            if (!empty($search['brand'])) $query->whereLike('brand', '%'.$search['brand'].'%');

            if (!empty($search['price'])) $query->whereLike('price', '%'.$search['price'].'%');
            
            if (!empty($search['discounted_price'])) $query->whereLike('discounted_price', '%'.$search['discounted_price'].'%');
            
            if (isset($search['status'])) $query->where('status', $search['status']);
            
            if (isset($search['productType'])) $query->whereIn('product_type_id', $search['productType']);
            
            $products  = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            return Response::response(null, ['products' => $products->items(), 'totalRows' => $products->total()]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function searchProduct(Request $request) {
        try {
            $query = Product::select('id', 'name', 'price', 'brand', 'content', 'abreviation', 'type_sale')
            ->where('status', true)
            ->whereLike('name', '%'.$request->name.'%');

            if (!isset($request->type)) $query->whereNotIn('product_type_id', [2]);
            
            $products = $query->get();
            return Response::response(null, $products);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveProduct(Request $request) {
        try {
            $product = Product::where('barcode', $request->barcode)->first();
            if ($product) {
                return Response::response('El código de barras ingresado ya esta en tus registros.<br>Por favor verifica la información.', null, true, 409);
            }
            Product::create([
                'product_type_id'  => $request->product_type_id,
                'name'             => $request->name,
                'barcode'          => $request->barcode,
                'brand'            => $request->brand,
                'price'            => $request->price,
                'discounted_price' => $request->discounted_price,
                'type_sale'        => $request->type_sale,
                'content'          => $request->content,
                'abreviation'      => $request->abreviation,
                'description'      => $request->description,
                'stock'            => $request->stock,
                'created_by'       => auth()->user()->id,
            ]);
            return Response::response('El producto se guardó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editProduct(Request $request) {
        try {
            $product = Product::where('barcode', $request->barcode)->first();
            if ($product->id !== $request->id) {
                return Response::response('El código de barras ingresado ya esta en tus registros.<br>Por favor verifica la información.', null, true, 409);
            }
            $txt                       = 'modificó';
            $product                   = Product::find($request->id);
            $product->product_type_id  = $request->product_type_id;
            $product->name             = $request->name;
            $product->barcode          = $request->barcode;
            $product->brand            = $request->brand;
            $product->price            = $request->price;
            $product->discounted_price = $request->discounted_price;
            $product->type_sale        = $request->type_sale;
            $product->content          = $request->content;
            $product->abreviation      = $request->abreviation;
            $product->description      = $request->description;
            $product->stock            = $request->stock;
            if ($request->status === true || $request->status === false) {
                $product->status = $request->status;
                $txt             = $request->status ? 'activo' : 'desactivo';
            }
            $product->updated_by = auth()->user()->id;
            $product->save();
            return Response::response('El producto se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteProduct($id) {
        try{
            $product             = Product::find($id);
            $product->deleted_by = auth()->user()->id;
            $product->save();
            $product->delete();
            return Response::response('El producto se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
