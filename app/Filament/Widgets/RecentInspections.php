<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInspections extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Optimized: Selective column loading + eager loading
                function () {
                    return Inspection::select([
                        'id',
                        'inspection_date',
                        'status',
                        'product_id',
                        'line_id',
                        'defect_type_id',
                        'component_id',
                        'inspector_id',
                        'created_at',
                    ])->with([
                                'product:id,style_number',
                                'line:id,code',
                                'defectType:id,name,severity',
                                'component:id,name',
                                'inspector:id,name',
                            ])
                        ->latest('inspection_date')
                        ->latest('created_at')
                        ->limit(10);
                }
            )
            ->columns([
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.style_number')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('line.code')
                    ->label('Line')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pass' => 'success',
                        'reject' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('defectType.name')
                    ->label('Defect')
                    ->limit(30)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('defectType.severity')
                    ->label('Tingkat Keparahan')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('inspector.name')
                    ->label('Inspector')
                    ->toggleable(),
            ])
            ->heading('Inspeksi Terbaru (10 Terakhir)');
    }
}
