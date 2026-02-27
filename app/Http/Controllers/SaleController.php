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
use App\Models\Sale;

class SaleController extends Controller {
    public function sales() {

    }

    public function getSales() {

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
            
            $total = $discount ? $sale->subtotal - $discount : $sale->subtotal;

            DB::beginTransaction();
            $sale = Sale::create([
                'status_sale_id' => 1, // Activa
                'payment_method_id' => $sale->payment_method,
                'appointment_id'    => !empty($sale->appointment_id) ? $sale->appointment_id : null,
                'cash'              => (in_array($sale->payment_method, [1, 2])) ? $sale->amount_cash : null,
                'card'              => (in_array($sale->payment_method, [3, 4])) ? $sale->amount_cash : null,
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

    private function checkServices($services) {
        foreach ($services as $key => $s) {
            if (!$s->newRecord) { // Si no es un nuevo registro entonces nos indica que estan eliminando uno de los que agendo en la cita
                $service = AppointmentServiceStaff::find($s->id);
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
