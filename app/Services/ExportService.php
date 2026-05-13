<?php

namespace App\Services;

use App\Models\Inspection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Export Service
 *
 * Handles all export functionality for inspections:
 * - Excel exports with filters
 * - PDF reports with charts
 *
 * Uses caching for optimized performance.
 */
class ExportService extends BaseService
{
    /**
     * Cache TTL for export data (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Get the model class for this service.
     */
    protected function getModel(): string
    {
        return Inspection::class;
    }

    /**
     * Export inspections to Excel with filters.
     */
    public function exportToExcel(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fileName = $this->generateFileName('excel', $filters);

        return Excel::download(
            new \App\Exports\InspectionsExport($filters),
            $fileName
        );
    }

    /**
     * Export inspections to PDF.
     */
    public function exportToPdf(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->getReportData($filters);

        $pdf = Pdf::loadView('reports.inspection-pdf', [
            'inspections' => $data['inspections'],
            'filters' => $filters,
            'summary' => $data['summary'],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $fileName = $this->generateFileName('pdf', $filters);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Get report data with caching.
     */
    public function getReportData(array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('report_data', $filters);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = $this->buildQuery($filters);

            return [
                'inspections' => $query->get(),
                'summary' => $this->getSummary($filters),
            ];
        });
    }

    /**
     * Get optimized stats for reports.
     */
    public function getReportStats(array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('report_stats', $filters);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = Inspection::query();

            // Apply filters
            $this->applyFilters($query, $filters);

            // Single query for all stats
            $stats = $query->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as rejected,
                COUNT(DISTINCT product_id) as product_count,
                COUNT(DISTINCT line_id) as line_count
            ")->first();

            $passRate = $stats->total > 0
                ? round(($stats->passed / $stats->total) * 100, 2)
                : 0;

            // Top defects
            $topDefects = Inspection::selectRaw("
                defect_types.name,
                defect_types.severity,
                COUNT(*) as count
            ")
                ->join('defect_types', 'inspections.defect_type_id', '=', 'defect_types.id')
                ->where('status', 'reject')
                ->when(isset($filters['start_date']), fn($q) =>
                    $q->whereDate('inspection_date', '>=', $filters['start_date'])
                )
                ->when(isset($filters['end_date']), fn($q) =>
                    $q->whereDate('inspection_date', '<=', $filters['end_date'])
                )
                ->groupBy('defect_type_id', 'defect_types.name', 'defect_types.severity')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            return [
                'total' => $stats->total,
                'passed' => $stats->passed,
                'rejected' => $stats->rejected,
                'pass_rate' => $passRate,
                'product_count' => $stats->product_count,
                'line_count' => $stats->line_count,
                'top_defects' => $topDefects,
            ];
        });
    }

    /**
     * Build query with filters.
     */
    protected function buildQuery(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = Inspection::with([
            'product:id,style_number,description',
            'line:id,code,name',
            'defectType:id,name,severity',
            'component:id,name',
            'user:id,name',
        ]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('inspection_date', 'desc');
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters = []): void
    {
        if (!empty($filters['start_date'])) {
            $query->whereDate('inspection_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('inspection_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['line_id'])) {
            $query->where('line_id', $filters['line_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    /**
     * Get summary statistics.
     */
    protected function getSummary(array $filters = []): array
    {
        return $this->getReportStats($filters);
    }

    /**
     * Generate unique filename for export.
     */
    protected function generateFileName(string $type, array $filters = []): string
    {
        $dateStr = now()->format('Y-m-d_His');
        $prefix = 'inspections';

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $prefix = "report_{$filters['start_date']}_{$filters['end_date']}";
        }

        return match ($type) {
            'excel' => "{$prefix}_{$dateStr}.xlsx",
            'pdf' => "{$prefix}_{$dateStr}.pdf",
            default => "{$prefix}_{$dateStr}.{$type}",
        };
    }

    /**
     * Generate cache key from filters.
     */
    protected function getCacheKey(string $prefix, array $filters = []): string
    {
        ksort($filters);
        return "{$prefix}_" . md5(serialize($filters));
    }

    /**
     * Clear export cache.
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}