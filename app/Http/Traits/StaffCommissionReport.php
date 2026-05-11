<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Exports\StaffCommissionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AppointmentServiceStaff;

trait StaffCommissionReport {
    public static function staffCommissions(Request $request) {
        try {
            $totalRecords = self::totalStaffCommissions($request);
            if ($totalRecords === 0) {
                return Response::response('No hay registros con los criterios de búsqueda que ingresaste.', null, true, 409);
            }
            if (file_exists('reportes/Reporte de comisiones de staff.xlsx')) {
                unlink('reportes/Reporte de comisiones de staff.xlsx');
            }
            Excel::store(new StaffCommissionExport($request->all()), 'reportes/Reporte de comisiones de staff.xlsx', 'public_path');
            return Response::response('El reporte se generó correctamente<br>Revisa tus descargas..', 'Reporte de comisiones de staff.xlsx?v='.uniqid());
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    private static function totalStaffCommissions(Request $request) {
        $request = (object) $request->all();
        $query   = AppointmentServiceStaff::whereIn('staff_id', $request->staff)
        ->where(function ($q) {
            $q->where(function ($q2) {
                // Caso 1: viene de cita
                $q2->whereNotNull('appointment_id')
                ->whereHas('appointment', function ($q3) {
                    $q3->where('appointment_status_id', 5); // Finalizada
                });
            })
            ->orWhere(function ($q2) {
                // Caso 2: viene de venta
                $q2->whereNotNull('sale_id')
                ->whereHas('sale', function ($q3) {
                    $q3->where('status_sale_id', 1); // Activa
                });
            });
        });

        switch ($request->period) {
            case '12':
                $query->whereYear('created_at', $request->year);
                break;
            case '1':
                $query->whereYear('created_at', $request->year)->whereMonth('created_at', $request->range);
                break;
            case '0':
                $query->whereBetween('created_at', [$request->range[0].' 00:00:00', $request->range[1].' 23:59:59']);
                break;
        }

        return $query->count();
    }
}