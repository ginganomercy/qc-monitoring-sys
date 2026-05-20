<?php

namespace App\Exports;

use App\Models\Inspection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Inspections Excel Export
 *
 * Exports inspection data with optional filters:
 * - Date range (start_date, end_date)
 * - Product filter
 * - Line filter
 * - Status filter (pass/reject)
 */
class InspectionsExport implements FromView, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Inspection::with([
            'product:id,style_number,description',
            'line:id,code,name',
            'defectType:id,name,severity',
            'component:id,name',
            'user:id,name',
        ]);

        // Apply filters
        if (! empty($this->filters['start_date'])) {
            $query->whereDate('inspection_date', '>=', $this->filters['start_date']);
        }

        if (! empty($this->filters['end_date'])) {
            $query->whereDate('inspection_date', '<=', $this->filters['end_date']);
        }

        if (! empty($this->filters['product_id'])) {
            $query->where('product_id', $this->filters['product_id']);
        }

        if (! empty($this->filters['line_id'])) {
            $query->where('line_id', $this->filters['line_id']);
        }

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        $inspections = $query->orderBy('inspection_date', 'desc')->get();

        // Calculate summary
        $total = $inspections->count();
        $passed = $inspections->where('status', 'pass')->count();
        $rejected = $total - $passed;
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

        return view('reports.inspection-excel', [
            'inspections' => $inspections,
            'filters' => $this->filters,
            'summary' => [
                'total' => $total,
                'passed' => $passed,
                'rejected' => $rejected,
                'pass_rate' => $passRate,
            ],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
            ],
            3 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']],
            ],
            'A:Z' => [
                'alignment' => ['vertical' => 'center'],
            ],
        ];
    }

    /**
     * Get the query filters for caching.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
