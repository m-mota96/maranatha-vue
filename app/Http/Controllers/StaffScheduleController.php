<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Models\StaffSchedule;

class StaffScheduleController extends Controller {
    public function getSchedules(Request $request) {
        try {
            $schedules = StaffSchedule::where('staff_id', $request->id)->get();
            return Response::response(null, $schedules);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function saveSchedule(Request $request) {
        try {
            $schedule = [];
            for ($i = 0; $i < sizeof($request->days); $i++) { 
                $schedule[] = [
                    'staff_id'        => $request->id,
                    'day'             => $request->days[$i],
                    'start_time'      => date("H:i", strtotime($request->schedule[$i]['start_time'])),
                    'meal_start_time' => $request->schedule[$i]['start_break'] ? date("H:i", strtotime($request->schedule[$i]['start_break'])) : null,
                    'meal_end_time'   => $request->schedule[$i]['end_break'] ? date("H:i", strtotime($request->schedule[$i]['end_break'])) : null,
                    'end_time'        => date("H:i", strtotime($request->schedule[$i]['end_time'])),
                    'status'          => 1,
                    'created_by'      => auth()->user()->id,
                    'created_at'      => date('Y-m-d H:i:s')
                ];
            }
            StaffSchedule::insert($schedule);
            return Response::response('El horario se guardó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editSchedule(Request $request) {
        try {
            StaffSchedule::where('staff_id', $request->id)->delete();
            $schedule = [];
            for ($i = 0; $i < sizeof($request->days); $i++) { 
                $schedule[] = [
                    'staff_id'        => $request->id,
                    'day'             => $request->days[$i],
                    'start_time'      => date("H:i", strtotime($request->schedule[$i]['start_time'])),
                    'meal_start_time' => $request->schedule[$i]['start_break'] ? date("H:i", strtotime($request->schedule[$i]['start_break'])) : null,
                    'meal_end_time'   => $request->schedule[$i]['end_break'] ? date("H:i", strtotime($request->schedule[$i]['end_break'])) : null,
                    'end_time'        => date("H:i", strtotime($request->schedule[$i]['end_time'])),
                    'status'          => 1,
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
}
