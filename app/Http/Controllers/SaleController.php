<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Models\Appointment;
use App\Models\AppointmentServiceStaff;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\StatusSale;

class SaleController extends Controller {
    public function sales() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Sale', [
            'module'         => $module,
            'menu'           => Modules::modulesMenu(),
            'statusSales'    => StatusSale::whereNotIn('id', [3])->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::orderBy('name')->get()
        ]);
    }

    public function getSales(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at', 'payment_method_id', 'subtotal', 'total', 'status'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Sale::with([
                'appointment:id,customer_id',
                'appointment.customer:id,name',
                'appointment.services:id,name,time',
                'statusSale',
                'paymentMethod:id,name',
                'services',
                'createdBy:id,name',
                'updatedBy:id,name'
            ]);

            if (!empty($search['dates'])) $query->whereBetween('created_at', [$search['dates'][0], $search['dates'][1]]);

            if (!empty($search['customer'])) {
                $query->whereHas('appointment.customer', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['customer'].'%');
                });
            }

            if (!empty($search['payment_method'])) $query->where('payment_method_id', $search['payment_method']);

            if (!empty($search['subtotal'])) $query->where('subtotal', $search['subtotal']);

            if (!empty($search['total'])) $query->where('total', $search['total']);

            if (!empty($search['user_register'])) {
                $query->whereHas('createdBy', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['user_register'].'%');
                });
            }

            if (!empty($search['user_cancel'])) {
                $query->whereHas('updatedBy', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['user_cancel'].'%');
                });
            }

            if (!empty($search['status'])) $query->where('status_sale_id', $search['status']);
            
            $sales = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            return Response::response(null, ['sales' => $sales->items(), 'totalRows' => $sales->total()]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    public function getSale(Request $request) {
        try {
            $sale = Sale::with([
                'appointment:id',
                'appointment.services:id,name',
                'inventories:id,sale_id,product_id,price,quantity',
                'inventories.product:id,name,content,abreviation,brand,type_sale'
            ])->first();
            return Response::response(null, $sale);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    public function saveSale(Request $request) {
        try {
            $sale = json_decode($request->sale);
            
            if ($sale->payment_method === 4) { // Transferencia
                $request->validate([
                    'file' => 'required|file|mimes:jpg,jpeg,png|max:1024', // Tamaño en kilobytes (1024 KB = 1 MB)
                ], [
                    'file.required' => 'Por favor carga un archivo',
                    'file.mimes'    => 'La imagen debe estar en formato jpg o png.',
                    'file.max'      => 'La imagen debe pesar menos de 1 MB.'
                ]);

                $file     = $request->file('file');
            }

            $services = json_decode($request->services);
            $products = json_decode($request->products);

            // Si existe descuento se calcula el monto
            $discount = !empty($sale->discount) ? 
                ($sale->type_discount === 'amount' ? 
                    $sale->discount // Si el descuento es en monto solo se le asigna dicho valor
                    :
                    ($sale->subtotal * ($sale->discount / 100)) // Si es en porcentaje se hace el cálculo
                ) 
                :
                null;
            
            $total      = $discount ? $sale->subtotal - $discount : $sale->subtotal;
            $amountCash = $sale->total;
            $amountCard = $sale->total;
            switch ($sale->payment_method) {
                case 1: //Efectivo
                    $amountCard = 0;
                    break;
                case 2: // Efectivo y Tarjeta
                    $amountCash = $sale->amount_cash;
                    $amountCard = $sale->amount_card;
                    break;
                case 3: // Tarjeta
                    $amountCash = 0;
                    break;
                case 4: // Transferencia
                    $amountCash = 0;
                    $amountCard = $sale->amount_card;
                    break;
            }

            DB::beginTransaction();
            $sale = Sale::create([
                'status_sale_id' => 1, // Activa
                'payment_method_id' => $sale->payment_method,
                'appointment_id'    => !empty($sale->appointment_id) ? $sale->appointment_id : null,
                'cash'              => $amountCash,
                'card'              => $amountCard,
                'subtotal'          => $sale->subtotal,
                'discount'          => $sale->discount ? $sale->discount : null,
                'type_discount'     => $sale->discount ? $sale->type_discount : null,
                'total'             => $total,
                'observations'      => $sale->observations,
                'created_by'        => auth()->user()->id
            ]);

            if (sizeof($services) > 0) {
                // Registra nuevos servicios agregados al crear la venta y elimina los que se agendaron previamente en la cita (si es que eliminaron alguno).
                self::checkServices($services);
            }

            if(sizeof($products) > 0) {
                // Si en la venta agregan productos, en esta función se guardan en la DB.
                self::checkProducts($products, $sale->id);
            }

            if (!empty($sale->appointment_id)) {
                $appointment                        = Appointment::find($sale->appointment_id);
                $appointment->appointment_status_id = 5; // Finalizada
                $appointment->save();
            }

            DB::commit();
            return Response::response('La venta se registró correctamente');
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    public function editSale(Request $request) {
        try {
            $sale                 = Sale::find($request->id);
            $sale->observations   = $request->observations;
            $sale->status_sale_id = $request->status;
            $sale->updated_by     = auth()->user()->id;
            $sale->save();
            Response::response('La venta se canceló correctamente.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    private function checkServices($services) {
        foreach ($services as $key => $s) {
            if (!$s->newRecord) { // Si no es un nuevo registro entonces nos indica que estan eliminando uno de los que agendo en la cita
                $service             = AppointmentServiceStaff::find($s->id);
                $service->deleted_by = auth()->user()->id;
                $service->save();
                $service->delete();
            } else { // Si es nuevo registro es porque le realizaron otro distinto durante su estadia y se lo registraron al momento de la venta
                AppointmentServiceStaff::create([
                    'appointment_id' => $s->appointment_id,
                    'service_id'     => $s->service_id,
                    'staff_id'       => $s->staff_id,
                    'price'          => $s->price,
                    'start_time'     => date("H:i", strtotime($s->start_time)), // Convierto formato 12 Hrs a formato 24 Hrs
                    'end_time'       => date("H:i", strtotime($s->end_time)), // Convierto formato 12 Hrs a formato 24 Hrs
                ]);
            }
        }
        return;
    }

    private function checkProducts($products, $saleId) {
        foreach ($products as $key => $p) {
            Inventory::create([
                'product_id'   => $p->product_id,
                'reference_id' => 3, // Venta de producto
                'sale_id'      => $saleId,
                'type'         => 'output',
                'quantity'     => $p->quantity,
                'price'        => $p->price,
                'created_by'   => auth()->user()->id
            ]);
        }
        return;
    }
}
