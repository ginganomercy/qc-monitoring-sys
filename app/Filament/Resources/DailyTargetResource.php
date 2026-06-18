<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyTargetResource\Pages;
use App\Models\DailyTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyTargetResource extends Resource
{
    protected static ?string $model = DailyTarget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Perencanaan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Target Harian';

    protected static ?string $label = 'Target Harian';

    protected static ?string $pluralLabel = 'Target Harian';

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdminQC() ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdminQC() ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdminQC() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('line_id')
                    ->label('Line')
                    ->relationship('line', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('target_date')
                    ->label('Tanggal Target')
                    ->required()
                    ->default(now())
                    ->native(false),

                Forms\Components\TimePicker::make('target_time')
                    ->label('Jam Target')
                    ->seconds(false),

                Forms\Components\TextInput::make('target_quantity')
                    ->label('Jumlah Target')
                    ->required()
                    ->numeric()
                    ->default(200)
                    ->minValue(1)
                    ->maxValue(1000)
                    ->suffix('units'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('daily_targets.*')
            ->selectSub(
                \App\Models\Inspection::selectRaw('COUNT(*)')
                    ->whereColumn('line_id', 'daily_targets.line_id')
                    ->whereColumn('inspection_date', 'daily_targets.target_date')
                    ->where('status', 'pass'),
                'passed_count'
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('line.name')
                    ->label('Line')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Tanggal Target')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_time')
                    ->label('Jam Target')
                    ->time('H:i')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('target_quantity')
                    ->label('Target')
                    ->numeric()
                    ->sortable()
                    ->suffix(' units'),

                Tables\Columns\TextColumn::make('passed_count')
                    ->label('Berhasil (Pass)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('achievement')
                    ->label('Pencapaian')
                    ->state(function (DailyTarget $record): string {
                        if ($record->target_quantity <= 0) return '0%';
                        $percent = ($record->passed_count / $record->target_quantity) * 100;
                        return round($percent, 1) . '%';
                    })
                    ->badge()
                    ->color(function (DailyTarget $record): string {
                        $percent = $record->target_quantity > 0 ? ($record->passed_count / $record->target_quantity) * 100 : 0;
                        if ($percent >= 100) return 'success';
                        if ($percent >= 50) return 'warning';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('line')
                    ->label('Line')
                    ->relationship('line', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('target_date')
                    ->label('Tanggal Target')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('target_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('target_date', '<=', $date),
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
