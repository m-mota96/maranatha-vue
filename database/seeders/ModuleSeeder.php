<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules[0] = [
            'module_id' => null,
            'name' => 'Configuración',
            'target' => null,
            'icon' => 'fas, wrench',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[1] = [
            'module_id' => 1,
            'name' => 'Menu',
            'target' => 'configuracion_menu',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[2] = [
            'module_id' => 1,
            'name' => 'Usuarios',
            'target' => 'configuracion_usuarios',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[3] = [
            'module_id' => 1,
            'name' => 'Permisos',
            'target' => null,
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[4] = [
            'module_id' => 4,
            'name' => 'Permisos de usuarios',
            'target' => 'configuracion_permisos_usuarios',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[5] = [
            'module_id' => 4,
            'name' => 'Permisos de modulos',
            'target' => 'configuracion_permisos_modulos',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[6] = [
            'module_id' => null,
            'name' => 'Organización',
            'target' => null,
            'icon' => 'fas, sitemap',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[7] = [
            'module_id' => 7,
            'name' => 'Staff',
            'target' => null,
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[8] = [
            'module_id' => 8,
            'name' => 'Administrar Staff',
            'target' => 'organizacion_staff_staff',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[9] = [
            'module_id' => 8,
            'name' => 'Puestos',
            'target' => 'organizacion_staff_puestos',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[10] = [
            'module_id' => null,
            'name' => 'Operación',
            'target' => null,
            'icon' => 'fas, gears',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[11] = [
            'module_id' => 11,
            'name' => 'Servicios',
            'target' => 'operacion_servicios',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[12] = [
            'module_id' => 11,
            'name' => 'Productos',
            'target' => null,
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[13] = [
            'module_id' => 13,
            'name' => 'Administrar Productos',
            'target' => 'operacion_productos_productos',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[14] = [
            'module_id' => 13,
            'name' => 'Inventario',
            'target' => 'operacion_productos_inventario',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[15] = [
            'module_id' => null,
            'name' => 'Clientes',
            'target' => null,
            'icon' => 'fas, users',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[16] = [
            'module_id' => 16,
            'name' => 'Clientes',
            'target' => 'clientes_clientes',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[17] = [
            'module_id' => null,
            'name' => 'Contabilidad',
            'target' => null,
            'icon' => 'fas, chart-line',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[18] = [
            'module_id' => 18,
            'name' => 'Ventas',
            'target' => 'contabilidad_ventas',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modules[19] = [
            'module_id' => 18,
            'name' => 'Estadísticas',
            'target' => 'contabilidad_estadisticas',
            'icon' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        Module::insert($modules);
    }
}
