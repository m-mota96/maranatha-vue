<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Models\Appointment;

class AppointmentController extends Controller {
    public function saveAppointment(Request $request) {
        try {
            // dd($request->all());
            $appointment = Appointment::create([
                'customer_id'         => $request->customer_id,
                'appointment_status_id' => 1,
                'date'                => $request->date,
                'horary'              => $request->horary,
                'cost'                => 100,
                'created_by'          => auth()->user()->id
            ]);
            $services = $request->services;
            for ($i = 0; $i < sizeof($services); $i++) { 
                $appointment->services()->attach($services[$i]['id'], [
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
