<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Sale;

trait SaleReport {
    public static function sales(Request $request) {
        try {
            $totalRecords = self::totalSales($request);
            if ($totalRecords === 0) {
                return Response::response('No hay registros con los criterios de búsqueda que ingresaste.', null, true, 409);
            }
            if (file_exists('reportes/Reporte de ventas.xlsx')) {
                unlink('reportes/Reporte de ventas.xlsx');
            }
            Excel::store(new SalesExport($request->all()), 'reportes/Reporte de ventas.xlsx', 'public_path');
            return Response::response('El reporte se generó correctamente<br>Revisa tus descargas..', 'Reporte de ventas.xlsx?v='.uniqid());
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    private static function totalSales(Request $request) {
        $request     = (object) $request->all();
        $status_sale = [1];
        if ($request->canceled_sales === 'true') $status_sale = [1, 2];
        $query = Sale::whereIn('status_sale_id', $status_sale);

        switch ($request->period) {
            case '12':
                $query->whereYear('created_at', $request->year);
                break;
            case '6':
            case '3':
                $dates = explode(',', $request->range);
                $query->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
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