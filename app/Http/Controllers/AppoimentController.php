<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Models\Appoiment;

class AppoimentController extends Controller {
    public function saveAppoiment(Request $request) {
        try {
            // dd($request->all());
            $appoiment = Appoiment::create([
                'customer_id'         => $request->customer_id,
                'appoiment_status_id' => 1,
                'date'                => $request->date,
                'horary'              => $request->horary,
                'cost'                => 100,
                'created_by'          => auth()->user()->id
            ]);
            $services = $request->services;
            for ($i = 0; $i < sizeof($services); $i++) { 
                $appoiment->services()->attach($services[$i]['id'], [
                    'staff_id'         => $services[$i]['staff_id'],
                    'price'            => $services[$i]['price'],
                    'discounted_price' => $services[$i]['discounted_price'],
                    'start_time'       => $services[$i]['start_time'],
                    'end_time'         => $services[$i]['end_time'],
                ]);
            }
            return Response::response('La cita se agendó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
