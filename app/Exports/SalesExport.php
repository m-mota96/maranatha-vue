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
use App\Models\Sale;

class SalesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {

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
            'Método de pago',
            'Fecha de registro',
            'Estatus',
            'Observaciones',
            'Usuario que registró',
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
            'D' => 20,
            'E' => 15,
            'F' => 50,
            'G' => 45,
            'H' => 15,
            'I' => 15,
            'J' => 15,
        ];
    }

    public function collection() {
        $status_sale = [1];
        if ($this->options->canceled_sales === 'true') $status_sale = [1, 2];
        $query = Sale::with([
            'appointment:id,customer_id',
            'appointment.customer:id,name',
            'customer:id,name',
            'statusSale',
            'paymentMethod:id,name',
            'createdBy:id,name'
        ])
        ->select(
            'id',
            'status_sale_id',
            'appointment_id',
            'customer_id',
            'subtotal',
            'discount',
            'type_discount',
            'total',
            'observations',
            'payment_method_id',
            'created_by'
        )
        ->selectRaw('date_format(created_at, "%d/%m/%Y") AS date')
        ->whereIn('status_sale_id', $status_sale);

        switch ($this->options->period) {
            case '12':
                $query->whereYear('created_at', $this->options->year);
                break;
            case '6':
            case '3':
                $dates = explode(',', $this->options->range);
                $query->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                break;
            case '1':
                $query->whereYear('created_at', $this->options->year)->whereMonth('created_at', $this->options->range);
                break;
            case '0':
                $query->whereBetween('created_at', [$this->options->range[0].' 00:00:00', $this->options->range[1].' 23:59:59']);
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
            $sale->paymentMethod->name,
            $sale->date,
            optional($sale->statusSale)->name,
            $sale->observations,
            $sale->createdBy->name,
            $sale->subtotal,
            $sale->discount,
            $sale->total,
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:Z1000')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        // Estilos para la fila 1
        $sheet->getStyle('A1:J1')->applyFromArray([
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
            'H' => '"$"#,##0.00',
            'I' => '"$"#,##0.00',
            'J' => '"$"#,##0.00',
        ];
    }
}
