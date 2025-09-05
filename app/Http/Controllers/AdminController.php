<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;
use App\Models\ServiceType;

class AdminController extends Controller {
    public function index() {
        $serviceTypes = ServiceType::with(['services:id,service_type_id,name,price,discounted_price,time,color'])
        ->where('status', 1)
        ->orderBy('name')
        ->get()
        ->map(function ($serviceType) {
            $serviceType->services = $serviceType->services->map(function ($service) {
                $service->active   = false;
                $service->quantity = 1;
                return $service;
            });
            return $serviceType;
        });
        return Inertia::render('admin/Home', [
            'menu'        => Modules::modulesMenu(),
            'serviceType' => $serviceTypes
        ]);
    }
}
