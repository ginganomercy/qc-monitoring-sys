<?php

namespace App\Filament\Resources\DailyTargetResource\Pages;

use App\Filament\Resources\DailyTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyTarget extends EditRecord
{
    protected static string $resource = DailyTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
