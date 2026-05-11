<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Http\Traits\Response;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\AppointmentServiceStaff;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Sale;

class StatisticController extends Controller {
    public function statistics() {
        $target = collect(request()->segments())->last();
        $module = Modules::module($target);
        if (empty($module)) {
            return redirect('administrador/inicio');
        }

        return Inertia::render('admin/Statistic', [
            'module'         => $module,
            'menu'           => Modules::modulesMenu()
        ]);
    }

    public function mostPopularServices(Request $request) { // Obtiene los servicios mas vendidos por mes, año y día
        try {
            $services = self::getPopularServicesData(
                $request->year, 
                $request->month, 
                $request->day, 
                $request->limit ?? 10
            );
            return Response::response(null, $services);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function salesIncome(Request $request) {
        try {
            $salesForDay   = Sale::selectRaw('IF(SUM(total) IS NULL, 0, SUM(total)) AS total')
            ->where('status_sale_id', 1)
            ->whereDate('created_at', $request->date)
            ->first();
            $salesForMonth = Sale::selectRaw('IF(SUM(total) IS NULL, 0, SUM(total)) AS total')
            ->where('status_sale_id', 1)
            ->whereMonth('created_at', $request->month)
            ->first();
            $salesForYear  = Sale::selectRaw('IF(SUM(total) IS NULL, 0, SUM(total)) AS total')
            ->where('status_sale_id', 1)
            ->whereYear('created_at', $request->year)
            ->first();
            return Response::response(null, [
                'salesForDay'   => $salesForDay->total,
                'salesForMonth' => $salesForMonth->total,
                'salesForYear'  => $salesForYear->total,
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function expenses(Request $request) {
        try {
            $expensesForDay   = Inventory::selectRaw('IF(SUM(product_cost) IS NULL, 0, SUM(product_cost)) AS total')
            ->where('type', 'input')
            ->where('reference_id', 1)
            ->whereDate('created_at', $request->date)->first();
            $expensesForMonth = Inventory::selectRaw('IF(SUM(product_cost) IS NULL, 0, SUM(product_cost)) AS total')
            ->where('type', 'input')
            ->where('reference_id', 1)
            ->whereMonth('created_at', $request->month)->first();
            $expensesForYear  = Inventory::selectRaw('IF(SUM(product_cost) IS NULL, 0, SUM(product_cost)) AS total')
            ->where('type', 'input')
            ->where('reference_id', 1)
            ->whereYear('created_at', $request->year)->first();
            return Response::response(null, [
                'expensesForDay'   => $expensesForDay->total,
                'expensesForMonth' => $expensesForMonth->total,
                'expensesForYear'  => $expensesForYear->total,
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function appointments(Request $request) {
        try {
            $start_date        = Carbon::parse($request->year.'-'.$request->month.'-01');
            $month             = $request->month;
            $year              = $request->year;
            $end_day           = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
            $end_date          = Carbon::parse($end_day);
            $arrayAppointments = [];

            $appointments = Appointment::whereYear('date', $year)->whereMonth('date', $month)->whereIn('appointment_status_id', [4, 5])->count();

            for($date = $start_date; $date->lte($end_date); $date->addDay()) {
                $count = Appointment::where('date', $date->format('Y-m-d'))->whereIn('appointment_status_id', [4, 5])->count();
                $arrayAppointments[$date->format('Y-m-d')] = $count;
            }

            return Response::response(null, [ 'perMonth' => $arrayAppointments, 'total' => $appointments]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function usersActiveInactice(Request $request) {
        try {
            $allowedValues = [2, 3, 6, 9, 12];
            $months        = in_array($request->months ?? '', $allowedValues) ? $request->months : 2;

            $actives = Customer::where('status', 1)
            ->whereHas('appointments', function ($q) use($months) {
                $q->where('date', '>=', now()->subMonths($months));
            })
            ->orWhereHas('sales', function($q) use($months) {
                $q->where('created_at', '>=', now()->subMonths($months));
            })
            ->count();
            
            $inactives = Customer::where('status', 1)
            ->where(function ($query) use ($months) {
                // No tiene citas recientes
                $query->whereDoesntHave('appointments', function ($q) use ($months) {
                    $q->where('date', '>=', now()->subMonths($months));
                });
            })
            ->where(function ($query) use ($months) {
                // No tiene compras recientes
                $query->whereDoesntHave('sales', function ($q) use ($months) {
                    $q->where('created_at', '>=', now()->subMonths($months));
                });
            })
            ->count();

            return Response::response(null, [
                'actives'   => $actives,
                'inactives' => $inactives
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function getAllStatistics(Request $request) {
        try {
            $salesCash = Sale::selectRaw('IF(SUM(cash) IS NULL, 0, SUM(cash)) AS total')
            ->whereIn('payment_method_id', [1, 2]) // [Efectivo, Efectivo y Tarjeta]
            ->where('status_sale_id', 1)
            ->whereDate('created_at', date('Y-m-d'))
            ->first();
            $salesCard = Sale::selectRaw('IF(SUM(card) IS NULL, 0, SUM(card)) AS total')
            ->whereIn('payment_method_id', [2, 3]) // [Efectivo y Tarjeta, Tarjeta]
            ->where('status_sale_id', 1)
            ->whereDate('created_at', date('Y-m-d'))
            ->first();
            $salesTransfer = Sale::selectRaw('IF(SUM(card) IS NULL, 0, SUM(card)) AS total')
            ->where('payment_method_id', 4) // [Transferencia]
            ->where('status_sale_id', 1)
            ->whereDate('created_at', date('Y-m-d'))
            ->first();
            $services = self::getPopularServicesData(date('Y'), date('m'), date('d'));

            $start_date            = Carbon::parse($request->year.'-'.$request->month.'-01');
            $month                 = $request->month;
            $year                  = $request->year;
            $end_day               = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
            $end_date              = Carbon::parse($end_day);
            $arraySales            = [];
            $arraySalesYear        = [];
            $arrayExpenses         = [];
            $arrayExpensesYear     = [];
            $totalSalesForMonth    = 0;
            $totalExpensesForMonth = 0;
            $totalSalesForYear     = 0;
            $totalExpensesForYear  = 0;

            for($date = $start_date; $date->lte($end_date); $date->addDay()) {
                $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
                ->whereDate('created_at', '=', $date->format('Y-m-d'))
                ->where('status_sale_id', 1) // Activa
                ->first();
                $arraySales[intval($date->format('d'))] = floatval($sales->total);
                $totalSalesForMonth = $totalSalesForMonth + floatval($sales->total);
                $expenses = Inventory::selectRaw('IF(SUM(product_cost) IS NOT NULL, SUM(product_cost), 0) AS total')
                ->where('type', 'input') // Ingreso
                ->where('reference_id', 1) // Abastecimiento de producto
                ->whereDate('created_at', '=', $date->format('Y-m-d'))
                ->first();
                $arrayExpenses[intval($date->format('d'))] = floatval($expenses->total);
                $totalExpensesForMonth = $totalExpensesForMonth + floatval($expenses->total);
            }

            $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            for ($i = 1; $i < 13; $i++) { 
                $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
                ->whereYear('created_at', $request->currentYear)
                ->whereMonth('created_at', $i)
                ->where('status_sale_id', 1) // Activa
                ->first();
                $arraySalesYear[$months[$i - 1]] = floatval($sales->total);
                $totalSalesForYear = $totalSalesForYear + floatval($sales->total);
                $expenses = Inventory::selectRaw('IF(SUM(product_cost) IS NOT NULL, SUM(product_cost), 0) AS total')
                ->where('type', 'input') // Ingreso
                ->where('reference_id', 1) // Abastecimiento de producto
                ->whereYear('created_at', $request->currentYear)
                ->whereMonth('created_at', $i)
                ->first();
                $arrayExpensesYear[$months[$i - 1]] = floatval($expenses->total);
                $totalExpensesForYear = $totalExpensesForYear + floatval($expenses->total);
            }

            return Response::response(null, [
                'salesCash'             => floatval($salesCash->total),
                'salesCard'             => floatval($salesCard->total),
                'salesTransfer'         => floatval($salesTransfer->total),
                'servicesMostPopular'   => $services,
                'salesForMonth'         => $arraySales,
                'expensesForMonth'      => $arrayExpenses,
                'totalSalesForMonth'    => $totalSalesForMonth,
                'totalExpensesForMonth' => $totalExpensesForMonth,
                'salesForYear'          => $arraySalesYear,
                'expensesForYear'       => $arrayExpensesYear,
                'totalSalesForYear'     => $totalSalesForYear,
                'totalExpensesForYear'  => $totalExpensesForYear
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    private function getPopularServicesData($year = null, $month = null, $day = null, $limit = 10) {
        return AppointmentServiceStaff::with(['service:id,name,color,time'])
        ->select('service_id')
        ->selectRaw('COUNT(*) AS total')
        ->whereHas('service', fn($q) => $q->where('require_staff', true))
        ->whereHas('appointment', function($q) use ($year, $month, $day) {
            $q->whereIn('appointment_status_id', [4, 5]); // [Confirmada, Finalizada]
            if ($year) $q->whereYear('date', $year);
            if ($month) $q->whereMonth('date', $month);
            if ($day) $q->whereDay('date', $day);
        })
        ->groupBy('service_id')
        ->orderByDesc('total')
        ->limit($limit)
        ->get();
    }
}
