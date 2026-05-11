<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Inventory;
use App\Models\Sale;

class IncomeExpensesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {
    public $options;

    public function __construct($options) {
        $this->options = (object) $options;
    }

    public function title(): string {
        return 'Hoja 1';
    }

    public function headings(): array {
        return [
            'Fecha',
            'Ingresos',
            'Egresos',
            'Diferencia',
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 15,
            'B' => 45,            
            'C' => 45,            
            'D' => 45,
        ];
    }

    public function collection() {
        return collect(self::getIncomeExpenses());
    }

    public function map($data): array {
        return [
            $data['date'],
            $data['incomes'],
            $data['expenses'],
            $data['incomes'] - $data['expenses']
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:Z1000')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        // Estilos para la fila 1
        $sheet->getStyle('A1:D1')->applyFromArray([
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
            'B' => '"$"#,##0.00',
            'C' => '"$"#,##0.00',
            'D' => '"$"#,##0.00',
        ];
    }

    private function getIncomeExpenses() {
        $format       = $this->options->daily_report === 'false' ? '%m/%Y' : '%d/%m/%Y';
        $formatCarbon = $this->options->daily_report === 'false' ? 'm/Y' : 'd/m/Y';
        $inventories  = Inventory::selectRaw('SUM(product_cost) AS total')
        ->selectRaw('DATE_FORMAT(created_at, "'.$format.'") AS date')
        ->where('reference_id', 1) // Abastecimiento de producto
        ->where('type', 'input'); // Ingreso

        $sales = Sale::selectRaw('SUM(total) AS total')
        ->selectRaw('DATE_FORMAT(created_at, "'.$format.'") AS date')
        ->where('status_sale_id', 1);

        switch ($this->options->period) {
            case '12':
                $inventories->whereYear('created_at', $this->options->year);
                $sales->whereYear('created_at', $this->options->year);
                break;
            case '6':
            case '3':
                $dates = explode(',', $this->options->range);
                $inventories->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                $sales->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                break;
            case '1':
                $inventories->whereYear('created_at', $this->options->year)->whereMonth('created_at', $this->options->range);
                $sales->whereYear('created_at', $this->options->year)->whereMonth('created_at', $this->options->range);
                break;
            case '0':
                $inventories->whereBetween('created_at', [$this->options->range[0].' 00:00:00', $this->options->range[1].' 23:59:59']);
                $sales->whereBetween('created_at', [$this->options->range[0].' 00:00:00', $this->options->range[1].' 23:59:59']);
                break;
        }
        $inventories->groupBy(DB::raw('DATE_FORMAT(created_at, "'.$format.'")'))->orderBy('date', 'DESC');

        $sales->groupBy(DB::raw('DATE_FORMAT(created_at, "'.$format.'")'))->orderBy('date', 'DESC');

        $expenses = $inventories->get()->toArray();
        $incomes  = $sales->get()->toArray();

        $expensesIndexed = collect($expenses)->keyBy('date');
        $incomesIndexed  = collect($incomes)->keyBy('date');

        $dates = collect($expenses)
        ->pluck('date')
        ->merge(collect($incomes)->pluck('date'))
        ->unique()
        ->sortBy(fn($date) => Carbon::createFromFormat($formatCarbon, $date))
        ->values();

        $data = $dates->map(function ($date) use ($expensesIndexed, $incomesIndexed) {
            return [
                'date'     => $date,
                'incomes'  => $incomesIndexed[$date]['total'] ?? '0.00',
                'expenses' => $expensesIndexed[$date]['total'] ?? '0.00',
            ];
        });
        return $data->toArray();
    }
}
