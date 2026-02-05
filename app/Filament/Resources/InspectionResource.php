<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
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

    protected static ?string $navigationGroup = 'QC Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inspection Details')
                    ->schema([
                        Forms\Components\DatePicker::make('inspection_date')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'style_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('style_number')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(),
                                Forms\Components\Textarea::make('description'),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ]),

                        Forms\Components\Select::make('line_id')
                            ->relationship('line', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pass' => 'Pass',
                                'reject' => 'Reject',
                            ])
                            ->required()
                            ->reactive()
                            ->default('pass'),

                        Forms\Components\Select::make('inspector_id')
                            ->relationship('inspector', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => auth()->id()),
                    ])->columns(2),

                Forms\Components\Section::make('Defect Information')
                    ->schema([
                        Forms\Components\Select::make('defect_type_id')
                            ->relationship('defectType', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn(Forms\Get $get) => $get('status') === 'reject'),

                        Forms\Components\Select::make('component_id')
                            ->relationship('component', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn(Forms\Get $get) => $get('status') === 'reject'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(fn(Forms\Get $get) => $get('status') === 'reject'),
                    ])->columns(2)
                    ->visible(fn(Forms\Get $get) => $get('status') === 'reject'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inspection_date')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.style_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('line.code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'pass',
                        'danger' => 'reject',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('defectType.name')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('component.name')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('inspector.name')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pass' => 'Pass',
                        'reject' => 'Reject',
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
