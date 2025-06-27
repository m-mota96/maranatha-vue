<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Authorization;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorizations[0] = [
            'module_id' => 2,
            'name' => 'Nuevo modulo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[1] = [
            'module_id' => 2,
            'name' => 'Editar modulo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[2] = [
            'module_id' => 2,
            'name' => 'Desactivar modulo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[3] = [
            'module_id' => 3,
            'name' => 'Nuevo usuario',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[4] = [
            'module_id' => 3,
            'name' => 'Editar usuario',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[5] = [
            'module_id' => 3,
            'name' => 'Desactivar usuario',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[6] = [
            'module_id' => 5,
            'name' => 'Ver permisos',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[7] = [
            'module_id' => 5,
            'name' => 'Actualizar permisos',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $authorizations[8] = [
            'module_id' => 6,
            'name' => 'Editar permisos',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Authorization::insert($authorizations);
    }
}
