<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModulePermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffScheduleController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('administrador')->name('administrador.')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('inicio', [AdminController::class, 'index'])->name('inicio');
    Route::get('configuracion_menu', [ModuleController::class, 'modules'])->name('configuracion_menu');
    Route::get('configuracion_usuarios', [AdminUserController::class, 'users'])->name('configuracion_usuarios');
    Route::get('configuracion_permisos_usuarios', [UserPermissionController::class, 'usersPermissions'])->name('configuracion_permisos_usuarios');
    Route::get('configuracion_permisos_modulos', [ModulePermissionController::class, 'modulesPermissions'])->name('configuracion_permisos_modulos');
    Route::get('organizacion_staff_staff', [StaffController::class, 'staff'])->name('organizacion_staff_staff');
    Route::get('organizacion_staff_puestos', [PositionController::class, 'positions'])->name('organizacion_staff_puestos');
    Route::get('operacion_servicios', [ServiceController::class, 'services'])->name('operacion_servicios');
    Route::get('operacion_productos', [ProductController::class, 'products'])->name('operacion_productos');
    Route::get('operacion_paquetes', [PackageController::class, 'packages'])->name('operacion_paquetes');
    Route::get('clientes_clientes', [CustomerController::class, 'customers'])->name('clientes_clientes');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('modules', [ModuleController::class, 'getModules']);
    Route::get('parentModules', [ModuleController::class, 'parentModules']);
    Route::get('modulesMenu', [ModuleController::class, 'modulesMenu']);
    Route::get('allModulesMenu', [ModuleController::class, 'allModulesMenu']);
    Route::get('userModules/userId', [ModuleController::class, 'userModules']);
    Route::post('module', [ModuleController::class, 'saveMenu']);
    Route::put('module', [ModuleController::class, 'editMenu']);
    Route::get('users', [AdminUserController::class, 'getUsers']);
    Route::post('user', [AdminUserController::class, 'saveUser']);
    Route::put('user', [AdminUserController::class, 'editUser']);
    Route::post('permissionUser', [UserPermissionController::class, 'permissionUser']);
    Route::get('services', [ServiceController::class, 'getServices']);
    Route::post('service', [ServiceController::class, 'saveService']);
    Route::put('service', [ServiceController::class, 'editService']);
    Route::delete('service/{id}', [ServiceController::class, 'deleteService']);
    Route::get('staff', [StaffController::class, 'getStaff']);
    Route::get('searchStaff', [StaffController::class, 'searchStaff']);
    Route::post('staff', [StaffController::class, 'saveStaff']);
    Route::put('staff', [StaffController::class, 'editStaff']);
    Route::delete('staff/{id}', [StaffController::class, 'deleteStaff']);
    Route::post('schedule', [StaffScheduleController::class, 'saveSchedule']);
    Route::put('schedule', [StaffScheduleController::class, 'editSchedule']);
    Route::get('positions', [PositionController::class, 'getPositions']);
    Route::post('position', [PositionController::class, 'savePosition']);
    Route::put('position', [PositionController::class, 'editPosition']);
    Route::delete('position/{id}', [PositionController::class, 'deletePosition']);
    Route::get('customers', [CustomerController::class, 'getCustomers']);
    Route::get('customer', [CustomerController::class, 'searchCustomer']);
    Route::post('customer', [CustomerController::class, 'saveCustomer']);
    Route::put('customer', [CustomerController::class, 'editCustomer']);
    Route::delete('customer/{id}', [CustomerController::class, 'deleteCustomer']);
    Route::post('appointment', [AppointmentController::class, 'saveAppointment']);
    Route::get('appointments', [AppointmentController::class, 'getAppointments']);
    Route::get('appointment/{id}', [AppointmentController::class, 'getAppointment']);
});