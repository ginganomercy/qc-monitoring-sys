<?php

namespace App\Filament\Resources\DailyTargetResource\Pages;

use App\Filament\Resources\DailyTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyTargets extends ListRecords
{
    protected static string $resource = DailyTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
