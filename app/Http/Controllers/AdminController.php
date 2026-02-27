<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Models\AppointmentStatus;
use App\Models\PaymentMethod;

class AdminController extends Controller {
    public function index() {
        return Inertia::render('admin/Home', [
            'menu'           => Modules::modulesMenu(),
            'status'         => AppointmentStatus::where('id', '<>', 3)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('status', true)->orderBy('name')->get()
        ]);
    }
}
