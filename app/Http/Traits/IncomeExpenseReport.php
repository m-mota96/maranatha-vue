<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Traits\Response;
use App\Exports\IncomeExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Inventory;
use App\Models\Sale;

trait IncomeExpenseReport {
    public static function incomeVsExpenses(Request $request) {
        try {
            $totalRecords = self::totalIncomeExpenses($request);
            if ($totalRecords === 0) {
                return Response::response('No hay registros con los criterios de búsqueda que ingresaste.', null, true, 409);
            }
            if (file_exists('reportes/Reporte de ingresos vs egresos.xlsx')) {
                unlink('reportes/Reporte de ingresos vs egresos.xlsx');
            }
            Excel::store(new IncomeExpensesExport($request->all()), 'reportes/Reporte de ingresos vs egresos.xlsx', 'public_path');
            return Response::response('El reporte se generó correctamente<br>Revisa tus descargas..', 'Reporte de ingresos vs egresos.xlsx?v='.uniqid());
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }

    private static function totalIncomeExpenses(Request $request) {
        $request     = (object) $request->all();
        $inventories = Inventory::where('reference_id', 1)->where('type', 'input');
        $sales       = Sale::where('status_sale_id', 1);

        switch ($request->period) {
            case '12':
                $inventories->whereYear('created_at', $request->year);
                $sales->whereYear('created_at', $request->year);
                break;
            case '6':
            case '3':
                $dates = explode(',', $request->range);
                $inventories->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                $sales->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                break;
            case '1':
                $inventories->whereYear('created_at', $request->year)->whereMonth('created_at', $request->range);
                $sales->whereYear('created_at', $request->year)->whereMonth('created_at', $request->range);
                break;
            case '0':
                $inventories->whereBetween('created_at', [$request->range[0].' 00:00:00', $request->range[1].' 23:59:59']);
                $sales->whereBetween('created_at', [$request->range[0].' 00:00:00', $request->range[1].' 23:59:59']);
                break;
        }

        return $inventories->count() + $sales->count();
    }
}