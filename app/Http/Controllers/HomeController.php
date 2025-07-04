<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller {
    public function index() {
        switch (auth()->user()->getRoleNames()[0]) {
            case 'superadmin':

                break;
            case 'admin':
                return redirect()->intended(route('administrador.inicio', absolute: false));
            case 'customer':
                
                break;
        }
    }
}
