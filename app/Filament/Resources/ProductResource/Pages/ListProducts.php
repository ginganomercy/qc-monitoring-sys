<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Optimize query with eager loading to prevent N+1 queries
     * Before: 1 query + N queries for each row's inspections_count
     * After: 1 query with JOIN for all counts
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()
            ->withCount('inspections'); // ✅ Single query instead of N+1
    }
}
