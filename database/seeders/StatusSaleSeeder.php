<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusSale;

class StatusSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data[0] = [
            'name' => 'Activa'
        ];

        $data[1] = [
            'name' => 'Cancelada'
        ];

        $data[2] = [
            'name' => 'Eliminada'
        ];

        StatusSale::insert($data);
    }
}
