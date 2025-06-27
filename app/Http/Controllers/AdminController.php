<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\Modules;

class AdminController extends Controller {
    public function index() {
        return Inertia::render('admin/Home', [
            'menu' => Modules::modulesMenu()
        ]);
    }
}
