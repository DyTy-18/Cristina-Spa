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

class ClientesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Collection $filas,
        private ?string    $desde,
        private ?string    $hasta
    ) {}

    public function title(): string
    {
        return $this->desde
            ? 'Clientes ' . $this->desde . ' a ' . $this->hasta
            : 'Reporte de Clientes';
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellido',
            'Teléfono',
            'Email',
            'Fecha nacimiento',
            'Visitas en período',
            'Total gastado (Bs.)',
            'Primera visita',
            'Última visita',
            'Servicios utilizados',
        ];
    }

    public function collection(): Collection
    {
        return $this->filas->map(fn($fila) => [
            $fila['cliente']->nombre ?? '',
            $fila['cliente']->apellido ?? '',
            $fila['cliente']->telefono ?? '',
            $fila['cliente']->email ?? '',
            $fila['cliente']->fecha_nacimiento?->format('d/m/Y') ?? '',
            $fila['visitas'],
            number_format($fila['total_gastado'], 2, '.', ''),
            $fila['primera_visita'] ? \Carbon\Carbon::parse($fila['primera_visita'])->format('d/m/Y') : '',
            $fila['ultima_visita'] ? \Carbon\Carbon::parse($fila['ultima_visita'])->format('d/m/Y') : '',
            $fila['servicios']->implode(', '),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = max($sheet->getHighestRow(), 2);

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C2C2C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('G2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
