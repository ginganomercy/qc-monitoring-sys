<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use App\Helpers\CacheHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInspection extends EditRecord
{
    protected static string $resource = InspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Preserve original inspector_id on edit.
     * The inspector who created the record should remain unchanged.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = $this->record->user_id;

        return $data;
    }

    /**
     * Invalidate dashboard caches after inspection is updated.
     */
    protected function afterSave(): void
    {
        CacheHelper::clearQcCaches();
    }
}
