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
            $query = AppointmentServiceStaff::with(['service:id,name,color,time'])
            ->select('service_id')
            ->selectRaw('COUNT(*) AS total')
            ->whereHas('service', function($q) {
                $q->where('require_staff', true);
            })->whereHas('appointment', function($q) {
                $q->whereIn('appointment_status_id', [4, 5]); // Confirmada o Finalizada
            });

            if (!empty($request->year)) {
                $query->whereHas('appointment', function ($q) use($request) {
                    $q->whereYear('date', $request->year);
                });
            }

            if (!empty($request->month)) {
                $query->whereHas('appointment', function ($q) use($request) {
                    $q->whereMonth('date', $request->month);
                });
            }

            if (!empty($request->day)) {
                $query->whereHas('appointment', function ($q) use($request) {
                    $q->whereDay('date', $request->day);
                });
            }

            $services = $query->groupBy('service_id')
            ->orderByDesc('total')
            ->limit($request->limit)
            ->get();
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
            ->count();
            
            $inactives = Customer::where('status', 1)
            ->whereHas('appointments', function ($q) use($months) {
                $q->where('date', '<', now()->subMonths($months));
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
}
