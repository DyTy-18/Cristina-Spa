<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class FinanzasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private array  $data,
        private string $desde,
        private string $hasta
    ) {}

    public function title(): string
    {
        return 'Finanzas ' . $this->desde . ' a ' . $this->hasta;
    }

    public function headings(): array
    {
        return ['Tipo', 'Fecha', 'Descripción / Empleado', 'Categoría / Tipo Pago', 'Monto (Bs.)'];
    }

    public function collection(): Collection
    {
        $rows = collect();

        // Resumen
        $rows->push(['RESUMEN', '', '', '', '']);
        $rows->push(['Ingresos (citas completadas)', '', '', '', number_format($this->data['ingresosCitas'], 2, '.', '')]);
        $rows->push(['Gastos operativos', '', '', '', number_format($this->data['totalGastos'], 2, '.', '')]);
        $rows->push(['Pagos a empleados', '', '', '', number_format($this->data['totalPagos'], 2, '.', '')]);
        $rows->push(['NETO', '', '', '', number_format($this->data['neto'], 2, '.', '')]);
        $rows->push(array_fill(0, 5, ''));

        // Gastos
        if ($this->data['gastos']->isNotEmpty()) {
            $rows->push(['GASTOS OPERATIVOS', '', '', '', '']);
            foreach ($this->data['gastos'] as $g) {
                $rows->push([
                    'Gasto',
                    $g->fecha->format('d/m/Y'),
                    $g->descripcion,
                    $g->categoria,
                    number_format($g->monto, 2, '.', ''),
                ]);
            }
            $rows->push(['Subtotal gastos', '', '', '', number_format($this->data['totalGastos'], 2, '.', '')]);
            $rows->push(array_fill(0, 5, ''));
        }

        // Pagos a empleados
        if ($this->data['pagos']->isNotEmpty()) {
            $rows->push(['PAGOS A EMPLEADOS', '', '', '', '']);
            foreach ($this->data['pagos'] as $p) {
                $rows->push([
                    'Pago',
                    $p->fecha_pago->format('d/m/Y'),
                    $p->empleado?->nombre . ' ' . ($p->empleado?->apellido ?? ''),
                    $p->tipo,
                    number_format($p->monto, 2, '.', ''),
                ]);
            }
            $rows->push(['Subtotal pagos', '', '', '', number_format($this->data['totalPagos'], 2, '.', '')]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Cabecera de columnas
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C2C2C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Resaltar filas de sección y totales
        for ($row = 2; $row <= $lastRow; $row++) {
            $cell = (string) $sheet->getCell('A' . $row)->getValue();
            if (in_array($cell, ['RESUMEN', 'GASTOS OPERATIVOS', 'PAGOS A EMPLEADOS'])) {
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F0E8']],
                ]);
            }
            if ($cell === 'NETO') {
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC9A96E']],
                ]);
            }
            if (str_starts_with($cell, 'Subtotal')) {
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true],
                ]);
            }
        }

        $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}
