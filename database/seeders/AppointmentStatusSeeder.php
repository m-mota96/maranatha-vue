<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppointmentStatus;

class AppointmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data[0] = [
            'name' => 'Agendada'
        ];

        $data[1] = [
            'name' => 'Cancelada'
        ];

        $data[2] = [
            'name' => 'Eliminada'
        ];

        $data[3] = [
            'name' => 'Confirmada'
        ];

        $data[4] = [
            'name' => 'Finalizada'
        ];

        AppointmentStatus::insert($data);
    }
}
