<?php

namespace App\Filament\Resources\LineResource\Pages;

use App\Filament\Resources\LineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLines extends ListRecords
{
    protected static string $resource = LineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Optimize query with eager loading to prevent N+1 queries
     * Before: 1 query + 2N queries (inspections_count + dailyTargets_count for each row)
     * After: 1 query with JOIN for all counts
     * Impact: Massive improvement - 6 rows = 13 queries → 1 query
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()
            ->withCount([
                'inspections',    // ✅ COUNT(inspections)
                'dailyTargets',   // ✅ COUNT(daily_targets)
            ]);
    }
}
