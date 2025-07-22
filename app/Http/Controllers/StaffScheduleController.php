<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Models\StaffSchedule;

class StaffScheduleController extends Controller {
    public function saveSchedule(Request $request) {
        try {
            $schedule = [];
            foreach ($request->schedule as $s) {
                $schedule[] = [
                    'staff_id'        => $s['staff_id'],
                    'day'             => $s['day'],
                    'start_time'      => ($s['status'] === 1) ? $s['start_time'] : null,
                    'end_time'        => ($s['status'] === 1) ? $s['end_time'] : null,
                    'meal_start_time' => ($s['status'] === 1) ? $s['meal_start_time'] : null,
                    'meal_end_time'   => ($s['status'] === 1) ? $s['meal_end_time'] : null,
                    'status'          => $s['status'],
                    'created_by'      => auth()->user()->id,
                    'created_at'      => date('Y-m-d H:i:s')
                ];
            }
            StaffSchedule::insert($schedule);
            return Response::response('El horario se modificó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editSchedule(Request $request) {
        try {
            foreach ($request->schedule as $s) {
                $schedule                  = StaffSchedule::find($s['id']);
                $schedule->start_time      = ($s['status'] === 1) ? $s['start_time'] : null;
                $schedule->end_time        = ($s['status'] === 1) ? $s['end_time'] : null;
                $schedule->meal_start_time = ($s['status'] === 1) ? $s['meal_start_time'] : null;
                $schedule->meal_end_time   = ($s['status'] === 1) ? $s['meal_end_time'] : null;
                $schedule->status          = $s['status'];
                $schedule->updated_by      = auth()->user()->id;
                $schedule->save();
            }
            return Response::response('El horario se modificó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
