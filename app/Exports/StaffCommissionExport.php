<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\AppointmentServiceStaff;

class StaffCommissionExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {
    public $options;

    public function __construct($options) {
        $this->options = (object) $options;
    }

    public function title(): string {
        return 'Hoja 1';
    }

    public function headings(): array {
        return [
            '#',
            'Staff',
            'Fecha',
            'No. de servicios realizados',
            'Importe de servicios realizados',
            'Comisión',
            'Importe de comisión',
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 5,
            'B' => 45,
            'C' => 25,
            'D' => 25,
            'E' => 35,
            'F' => 20,
            'G' => 20,
        ];
    }

    public function collection() {
        $query = AppointmentServiceStaff::with(['staff:id,name,first_name,last_name,commission'])
        ->select('staff_id', 'date')
        ->selectRaw('SUM(price) AS subtotal')
        ->selectRaw('COUNT(*) AS quantity')
        ->selectRaw('date_format(date, "%d/%m/%Y") AS date')
        ->whereIn('staff_id', $this->options->staff)
        ->where(function ($q) {
            $q->where(function ($q2) {
                // Caso 1: viene de cita
                $q2->whereNotNull('appointment_id')
                ->whereHas('appointment', function ($q3) {
                    $q3->where('appointment_status_id', 5); // Finalizada
                });
            })
            ->orWhere(function ($q2) {
                // Caso 2: viene de venta
                $q2->whereNotNull('sale_id')
                ->whereHas('sale', function ($q3) {
                    $q3->where('status_sale_id', 1); // Activa
                });
            });
        });

        switch ($this->options->period) {
            case '12':
                $query->whereYear('date', $this->options->year);
                break;
            case '1':
                $query->whereYear('date', $this->options->year)->whereMonth('date', $this->options->range);
                break;
            case '0':
                $query->whereBetween('date', [$this->options->range[0], $this->options->range[1]]);
                break;
        }
        $data = $query->groupBy('staff_id')->groupBy('date')->orderBy('date', 'ASC')->get();
        return $data;
    }

    public function map($item): array {
        $days     = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        $dateTime = strtotime($item->date);
        $day      = $days[date('w', $dateTime)];

        $commission       = intval($item->staff->commission) / 100;
        $amountCommission = $item->subtotal * $commission;

        return [
            $item->staff_id,
            $item->staff->first_name.' '.$item->staff->last_name.' '.$item->staff->name,
            // $day.' '.$item->date,
            $item->date,
            $item->quantity,
            $item->subtotal,
            $item->staff->commission.'%',
            $amountCommission,
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:Z1000')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        // Estilos para la fila 1
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '9BBB59' // Color verde
                ],
            ],
        ]);

        return [];
    }

    public function columnFormats(): array {
        return [
            'E' => '"$"#,##0.00',
            'G' => '"$"#,##0.00',
        ];
    }
}
