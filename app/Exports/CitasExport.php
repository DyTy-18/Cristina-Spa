<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CitasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Collection $citas,
        private string     $desde,
        private string     $hasta
    ) {}

    public function title(): string
    {
        return 'Citas ' . $this->desde . ' a ' . $this->hasta;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Cliente',
            'Servicios',
            'Precio (Bs.)',
            'Tipo de Pago',
            'Sucursal',
        ];
    }

    public function collection(): Collection
    {
        $rows = $this->citas->map(fn($cita) => [
            $cita->fecha->format('d/m/Y'),
            $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('H:i') : '—',
            $cita->cliente ? $cita->cliente->nombre . ' ' . $cita->cliente->apellido : '—',
            $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' + ') ?: '—',
            number_format((float) $cita->precio_final, 2, '.', ''),
            match ($cita->tipo_pago) {
                'efectivo' => 'Efectivo',
                'tarjeta'  => 'Tarjeta',
                'qr'       => 'QR',
                default    => '—',
            },
            $cita->sucursal?->nombre ?? '—',
        ]);

        // Fila de total
        $rows->push([
            'TOTAL', '', '', '',
            number_format($this->citas->sum('precio_final'), 2, '.', ''),
            '', '',
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C2C2C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A' . $lastRow . ':G' . $lastRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC9A96E']],
        ]);

        $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}
