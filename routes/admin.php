<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModulePermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('administrador')->name('administrador.')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('inicio', [AdminController::class, 'index'])->name('inicio');
    Route::get('configuracion_menu', [ModuleController::class, 'modules'])->name('configuracion_menu');
    Route::get('configuracion_usuarios', [UserController::class, 'users'])->name('configuracion_usuarios');
    Route::get('configuracion_permisos_usuarios', [UserPermissionController::class, 'usersPermissions'])->name('configuracion_permisos_usuarios');
    Route::get('configuracion_permisos_modulos', [ModulePermissionController::class, 'modulesPermissions'])->name('configuracion_permisos_modulos');
    Route::get('organizacion_staff_staff', [StaffController::class, 'staff'])->name('organizacion_staff_staff');
    Route::get('organizacion_staff_puestos', [PositionController::class, 'positions'])->name('organizacion_staff_puestos');
    Route::get('operacion_servicios', [ServiceController::class, 'services'])->name('operacion_servicios');
    Route::get('operacion_productos', [ProductController::class, 'products'])->name('operacion_productos');
    Route::get('operacion_paquetes', [PackageController::class, 'packages'])->name('operacion_paquetes');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('modules', [ModuleController::class, 'getModules']);
});