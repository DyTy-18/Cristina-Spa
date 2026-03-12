<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class ComisionesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Collection $porEmpleado,
        private string     $desde,
        private string     $hasta
    ) {}

    public function title(): string
    {
        return 'Comisiones ' . $this->desde . ' a ' . $this->hasta;
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Cargo',
            'Fecha',
            'Cliente',
            'Servicio',
            'Precio (Bs.)',
            'Comisión %',
            'Co-partic.',
            'Monto comisión (Bs.)',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->porEmpleado as $bloque) {
            foreach ($bloque['filas'] as $fila) {
                $rows->push([
                    $bloque['empleado']?->nombre_completo ?? '—',
                    $bloque['empleado']?->cargo ?? '—',
                    $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') : '—',
                    $fila['cliente'] ?? '—',
                    $fila['servicio'],
                    number_format($fila['precio'], 2, '.', ''),
                    $fila['com_estado'] === 'activa' ? number_format($fila['pct'], 2, '.', '') . '%' : $fila['com_estado'],
                    $fila['participantes'] > 1 ? $fila['participantes'] . ' personas' : '—',
                    number_format($fila['monto_comision'], 2, '.', ''),
                ]);
            }

            // Subtotal row per employee
            $rows->push([
                'SUBTOTAL — ' . ($bloque['empleado']?->nombre_completo ?? ''),
                '', '', '', '',
                number_format($bloque['total_precio'], 2, '.', ''),
                '', '',
                number_format($bloque['total_comision'], 2, '.', ''),
            ]);

            // Blank separator
            $rows->push(array_fill(0, 9, ''));
        }

        // Grand total
        $rows->push([
            'TOTAL GENERAL', '', '', '', '',
            number_format($this->porEmpleado->sum('total_precio'), 2, '.', ''),
            '', '',
            number_format($this->porEmpleado->sum('total_comision'), 2, '.', ''),
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C2C2C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style subtotal / total rows
        for ($row = 2; $row <= $lastRow; $row++) {
            $cell = $sheet->getCell('A' . $row)->getValue();
            if (str_starts_with((string) $cell, 'SUBTOTAL')) {
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F0E8']],
                ]);
            }
            if (str_starts_with((string) $cell, 'TOTAL GENERAL')) {
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC9A96E']],
                ]);
            }
        }

        // Right-align numeric columns
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I2:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}
