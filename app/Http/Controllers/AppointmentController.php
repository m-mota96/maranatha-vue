<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Response;
use App\Models\Appointment;
use App\Models\AppointmentServiceStaff;
use App\Models\AppointmentStatus;

class AppointmentController extends Controller {
    public function saveAppointment(Request $request) {
        try {
            if (sizeof($request->services) === 0) {
                return Response::response('No agregaste ningún servicio.', null, true, 409);
            }

            DB::beginTransaction();
            $appointment = Appointment::create([
                'customer_id'           => $request->customer_id,
                'appointment_status_id' => 1,
                'date'                  => $request->date,
                'horary'                => $request->horary,
                'cost'                  => $request->total,
                'created_by'            => auth()->user()->id
            ]);
            $services = $request->services;
            for ($i = 0; $i < sizeof($services); $i++) { 
                $appointment->services()->attach($services[$i]['id'], [
                    'staff_id'         => $services[$i]['staff_id'],
                    'price'            => $services[$i]['price'],
                    'discounted_price' => $services[$i]['discounted_price'],
                    'start_time'       => $services[$i]['start_time'],
                    'end_time'         => $services[$i]['end_time'],
                    'created_by'       => auth()->user()->id,
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s')
                ]);
            }
            DB::commit();
            return Response::response('La cita se agendó correctamente.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function getAppointments(Request $request) {
        try {
            $pagination = $request->pagination;
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $search     = $request->search;
            $order      = $request->order;

            $allowedColumns = ['created_at'];

            $orderBy = in_array($order['orderBy'] ?? '', $allowedColumns)
                ? $order['orderBy']
                : 'created_at';

            $orderDir = strtolower($order['order'] ?? '') === 'asc' ? 'asc' : 'desc';

            $query = Appointment::with(['customer:id,name,phone', 'status', 'services', 'createdBy:id,name', 'updatedBy:id,name'])
            ->where('date', $search['currentDate']);

            if (!empty($search['customer'])) {
                $query->whereHas('customer', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['customer'].'%');
                });
            }

            if (isset($search['status'])) $query->where('appointment_status_id', $search['status']);

            if (!empty($search['user'])) {
                $query->whereHas('createdBy', function($q) use($search) {
                    $q->whereLike('name', '%'.$search['user'].'%');
                });
            }

            $appointments = $query->orderBy($orderBy, $orderDir)->paginate($limit, ['*'], 'page', $pagination['currentPage']);
            $scheduled    = Appointment::where('date', $search['currentDate'])->where('appointment_status_id', 1)->count();
            $confirmed    = Appointment::where('date', $search['currentDate'])->where('appointment_status_id', 4)->count();
            $finished     = Appointment::where('date', $search['currentDate'])->where('appointment_status_id', 5)->count();
            $canceled     = Appointment::where('date', $search['currentDate'])->where('appointment_status_id', 2)->count();
            
            return Response::response(null, [
                'appointments' => $appointments->items(),
                'totalRows'    => $appointments->total(),
                'scheduled'    => $scheduled,
                'confirmed'    => $confirmed,
                'canceled'     => $canceled,
                'finished'     => $finished
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function getAppointment($id) {
        try {
            $services = AppointmentServiceStaff::with([
                'appointment:id,date',
                'service:id,name,time',
                'staff:id,name,first_name,last_name'
            ])->where('appointment_id', $id)
            ->orderBy('start_time')->get()
            ->map(function ($service) {
                $service->type      = 'Servicio';
                $service->newRecord = false;
                $service->deleted   = false;
                return $service;
            });
            return Response::response(null, $services);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function statusAppointment(Request $request) {
        try {
            $allowedStatuses = AppointmentStatus::pluck('id')->toArray();
            $appointment     = Appointment::find($request->id);

            $status = in_array($request->status ?? '', $allowedStatuses)
                ? $request->status
                : $appointment->appointment_status_id;

            $txt                                = '';
            $appointment->appointment_status_id = $status;
            switch ($status) {
                case 1: // Pendiente
                    $txt = 'reactivó';
                    $appointment->observations = null;
                    break;
                case 2: // Cancelada
                    $txt = 'canceló';
                    $appointment->observations = $request->observations;
                    break;
                case 4: // Confirmada
                    $txt = 'confirmó';
                    break;
            }
            $appointment->updated_by = auth()->user()->id;
            $appointment->save();
            return Response::response('La cita se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteAppointment($id) {
        try {
            $appointment                        = Appointment::find($id);
            $appointment->appointment_status_id = 3; // Eliminada
            $appointment->deleted_by            = auth()->user()->id;
            $appointment->save();
            $appointment->delete();
            return Response::response('La cita se eliminó correctamente.');
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
