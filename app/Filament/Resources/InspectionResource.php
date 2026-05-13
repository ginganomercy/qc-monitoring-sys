<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Helpers\CacheHelper;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Manajemen QC';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Inspeksi';

    protected static ?string $label = 'Inspeksi';

    protected static ?string $pluralLabel = 'Inspeksi';

    /**
     * Optimize table queries with eager loading
     * Prevents N+1 queries on inspection list
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'product:id,style_number',
                'line:id,code,name',
                'defectType:id,name,severity',
                'component:id,name',
                'inspector:id,name',
            ]);
    }

    /**
     * Cache navigation badge count (5 minutes TTL)
     * Reduces database hits on navigation render
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) CacheHelper::getResourceCount(
            static::getModel(),
            'inspections_count'
        );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Inspeksi')
                    ->schema([
                        Forms\Components\DatePicker::make('inspection_date')
                            ->label('Tanggal Inspeksi')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false),

                        Forms\Components\Select::make('product_id')
                            ->label('Produk')
                            ->relationship(
                                'product',
                                'style_number',
                                modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('style_number')
                                    ->label('Nomor Style')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]),

                        Forms\Components\Select::make('line_id')
                            ->label('Line')
                            ->relationship(
                                'line',
                                'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pass' => 'Lolos',
                                'reject' => 'Ditolak',
                            ])
                            ->required()
                            ->reactive()
                            ->default('pass'),

                        Forms\Components\Select::make('inspector_id')
                            ->label('Inspector')
                            ->relationship('inspector', 'name')
                            ->default(fn() => auth()->id())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Defect')
                    ->schema([
                        Forms\Components\Select::make('defect_type_id')
                            ->label('Jenis Defect')
                            ->relationship(
                                'defectType',
                                'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'reject')
                            ->visible(fn(Forms\Get $get): bool => $get('status') === 'reject'),

                        Forms\Components\Select::make('component_id')
                            ->label('Komponen')
                            ->relationship(
                                'component',
                                'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn(Forms\Get $get): bool => $get('status') === 'reject'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(fn(Forms\Get $get): bool => $get('status') === 'reject'),
                    ])->columns(2)
                    ->visible(fn(Forms\Get $get): bool => $get('status') === 'reject'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.style_number')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('line.code')
                    ->label('Line')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pass' => 'success',
                        'reject' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('defectType.name')
                    ->label('Jenis Defect')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('component.name')
                    ->label('Komponen')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('inspector.name')
                    ->label('Inspector')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pass' => 'Lolos',
                        'reject' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'style_number')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('line')
                    ->relationship('line', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('inspection_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('inspection_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('inspection_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('inspection_date', 'desc');
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
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
        ];
    }
}
