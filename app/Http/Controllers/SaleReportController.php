<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;

class SaleReportController extends Controller {
    public function salesReport() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/SaleReport', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }

    public function sales(Request $request) {
        try {
            if (file_exists('reportes/Reporte de ventas.xlsx')) {
                unlink('reportes/Reporte de ventas.xlsx');
            }
            Excel::store(new SalesExport($request->all()), 'reportes/Reporte de ventas.xlsx', 'public_path');
            return Response::response('El reporte se generó correctamente<br>Revisa tus descargas..', 'Reporte de ventas.xlsx?v='.uniqid());
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }
}
