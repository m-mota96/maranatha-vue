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
use App\Models\Inventory;

class InventoriesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {
    public $options;

    public function __construct($options) {
        $this->options = (object) $options;
    }

    public function title(): string {
        return 'Hoja 1';
    }

    public function headings(): array {
        return [
            'Producto',
            'Tipo de movimiento',
            'Referencia',
            'Proveedor',
            'Cantidad',
            'Costo del producto',
            'Lote',
            'Fecha de caducidad',
            'Fecha de registro',
            'Descripción',
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 50,
            'B' => 25,            
            'C' => 35,            
            'D' => 35,
            'E' => 15,
            'F' => 20,
            'G' => 15,
            'H' => 25,
            'I' => 25,
            'J' => 50
        ];
    }

    public function collection() {
        $inventories = Inventory::select('*')
        ->selectRaw('IF(expiration_date IS NOT NULL, DATE_FORMAT(expiration_date, "%d/%m/%Y"), "") AS expiration_date')
        ->selectRaw('DATE_FORMAT(created_at, "%d/%m/%Y") AS created_date')
        ->with([
            'product:id,name,content,abreviation,type_sale',
            'reference:id,name',
            'provider:id,name,seller'
        ])
        ->whereIn('reference_id', [1, 2]);

        switch ($this->options->period) {
            case '12':
                $inventories->whereYear('created_at', $this->options->year);
                break;
            case '6':
            case '3':
                $dates = explode(',', $this->options->range);
                $inventories->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                break;
            case '1':
                $inventories->whereYear('created_at', $this->options->year)->whereMonth('created_at', $this->options->range);
                break;
            case '0':
                $inventories->whereBetween('created_at', [$this->options->range[0].' 00:00:00', $this->options->range[1].' 23:59:59']);
                break;
        }
        $data = $inventories->get();
        // dd($data);
        return $data;
    }

    public function map($inventory): array {
        $product  = $inventory->product->name.($inventory->product->content ? ' '.$inventory->product->content : '').($inventory->product->content ? ' '.$inventory->product->abreviation : '');
        $provider = $inventory->provider ? $inventory->provider->name.($inventory->provider->seller ? ' - '.$inventory->provider->seller : '') : '';

        return [
            $product,
            $inventory->type === 'input' ? 'Ingreso' : 'Egreso',
            $inventory->reference->name,
            $provider,
            $inventory->product->type_sale === 'pza' ? intval($inventory->quantity) : $inventory->quantity,
            $inventory->product_cost,
            $inventory->batch,
            $inventory->expiration_date,
            $inventory->created_date,
            $inventory->description,
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
            'F' => '"$"#,##0.00',
        ];
    }
}
