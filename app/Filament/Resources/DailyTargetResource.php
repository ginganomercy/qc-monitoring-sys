<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyTargetResource\Pages;
use App\Models\DailyTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DailyTargetResource extends Resource
{
    protected static ?string $model = DailyTarget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Planning';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('line_id')
                    ->relationship('line', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('target_date')
                    ->required()
                    ->default(now())
                    ->native(false),

                Forms\Components\TextInput::make('target_quantity')
                    ->required()
                    ->numeric()
                    ->default(200)
                    ->minValue(1)
                    ->maxValue(1000)
                    ->suffix('units'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('line.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_date')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_quantity')
                    ->numeric()
                    ->sortable()
                    ->suffix(' units'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('line')
                    ->relationship('line', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('target_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('target_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('target_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('target_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyTargets::route('/'),
            'create' => Pages\CreateDailyTarget::route('/create'),
            'edit' => Pages\EditDailyTarget::route('/{record}/edit'),
        ];
    }
}
