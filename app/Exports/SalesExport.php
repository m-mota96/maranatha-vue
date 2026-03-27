<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Sale;

class SalesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle {

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
            'Cliente',
            'Fecha de registro',
            'Estatus',
            'Subtotal',
            'Descuento',
            'Total',
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 5,
            'B' => 45,            
            'C' => 20,            
            'D' => 25,
            'E' => 25,
            'F' => 15,
            'G' => 15,
        ];
    }

    public function collection() {
        // dd($this->options);
        $query = Sale::with(['appointment:id,customer_id', 'appointment.customer:id,name', 'customer:id,name', 'statusSale'])
        ->select('id', 'status_sale_id', 'appointment_id', 'customer_id', 'subtotal', 'discount', 'type_discount', 'total')
        ->selectRaw('date_format(created_at, "%d/%m/%Y") AS date');

        switch ($this->options->report_type) {
            case '12':
                dd(12);
                break;
            case '6':
            case '3':
                $query->whereBetween('created_at', explode(',', $this->options->range));
                break;
            case '1':
                dd(1);
                break;
            case '0':
                dd(0);
                break;
        }
        $data = $query->orderBy('created_at')->get();
        // dd($data);
        return $data;
    }

    public function map($sale): array {
        $customer = optional($sale->appointment?->customer)->name 
            ?? optional($sale->customer)->name
            ?? 'N/A';

        return [
            $sale->id,
            $customer,
            $sale->date,
            optional($sale->statusSale)->name,
            $sale->subtotal,
            $sale->discount,
            $sale->total,
        ];
    }

    public function styles(Worksheet $sheet) {
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
}
