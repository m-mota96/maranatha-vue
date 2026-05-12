<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\IncomeExpenseReport;
use App\Http\Traits\InventoryReport;
use App\Http\Traits\SaleReport;
use App\Http\Traits\StaffCommissionReport;

class ReportController extends Controller {
    use IncomeExpenseReport;
    use InventoryReport;
    use SaleReport;
    use StaffCommissionReport;

    public function salesReport() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Report', [
            'module' => $module,
            'menu'   => Modules::modulesMenu()
        ]);
    }
}
