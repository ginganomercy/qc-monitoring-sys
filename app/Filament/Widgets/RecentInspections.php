<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInspections extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inspection::query()
                    ->with(['product', 'line', 'defectType', 'component', 'inspector'])
                    ->latest('inspection_date')
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.style_number')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('line.code')
                    ->label('Line')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'pass',
                        'danger' => 'reject',
                    ]),

                Tables\Columns\TextColumn::make('defectType.name')
                    ->label('Defect')
                    ->limit(30)
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('defectType.severity')
                    ->label('Severity')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'critical',
                    ])
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('inspector.name')
                    ->label('Inspector')
                    ->toggleable(),
            ])
            ->heading('Recent Inspections (Latest 10)');
    }
}
