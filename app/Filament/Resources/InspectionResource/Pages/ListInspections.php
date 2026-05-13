<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInspections extends ListRecords
{
    protected static string $resource = InspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_report')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label('Mulai Tanggal')
                        ->required()
                        ->default(now()->startOfMonth()),
                    \Filament\Forms\Components\DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->required()
                        ->default(now()->endOfMonth()),
                    \Filament\Forms\Components\Select::make('format')
                        ->label('Format Laporan')
                        ->options([
                            'pdf' => 'PDF Document (.pdf)',
                            'excel' => 'Excel Spreadsheet (.xlsx)',
                        ])
                        ->required()
                        ->default('pdf'),
                ])
                ->action(function (array $data) {
                    $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
                    $endDate = \Carbon\Carbon::parse($data['end_date'])->endOfDay();
                    $filename = 'Laporan_QC_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd');

                    if ($data['format'] === 'excel') {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\InspectionsExport($startDate, $endDate),
                            $filename . '.xlsx'
                        );
                    }

                    // PDF format
                    $inspections = \App\Models\Inspection::with(['product', 'line', 'defectType', 'component', 'inspector'])
                        ->whereBetween('inspection_date', [$startDate, $endDate])
                        ->orderBy('inspection_date', 'asc')
                        ->get();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.inspection-pdf', [
                        'inspections' => $inspections,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ]);

                    return response()->streamDownload(fn () => print($pdf->output()), $filename . '.pdf');
                }),
            Actions\CreateAction::make(),
        ];
    }
}
