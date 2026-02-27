<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reference;

class ReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data[0] = [
            'name' => 'Abastecimiento de producto'
        ];

        $data[1] = [
            'name' => 'Ajuste de inventario'
        ];

        $data[2] = [
            'name' => 'Venta de producto'
        ];

        Reference::insert($data);
    }
}
