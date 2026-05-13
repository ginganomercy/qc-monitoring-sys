<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use App\Helpers\CacheHelper;
use Filament\Resources\Pages\CreateRecord;

class CreateInspection extends CreateRecord
{
    protected static string $resource = InspectionResource::class;

    /**
     * Force inspector_id to authenticated user.
     * Prevents form manipulation even if someone bypasses disabled field.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['inspector_id'] = auth()->id();

        return $data;
    }

    /**
     * Invalidate dashboard caches after new inspection is created.
     */
    protected function afterCreate(): void
    {
        CacheHelper::clearQcCaches();
    }
}
