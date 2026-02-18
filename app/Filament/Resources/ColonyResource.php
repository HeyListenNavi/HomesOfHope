<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColonyResource\Pages;
use App\Models\Colony;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ColonyResource extends Resource
{
    protected static ?string $model = Colony::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Colonias No Atendidas';
    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?string $modelLabel = 'Colonia';
    protected static ?string $pluralModelLabel = 'Colonias No Atendidas';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
	     ->schema([
                Forms\Components\Section::make('Datos de la Colonia')
		    ->columns(2)
	            ->schema([
			Forms\Components\TextInput::make('city')
			    ->label('Ciudad')
		            ->required(),
                        Forms\Components\TextInput::make('name')
			    ->label('Colonia')
			    ->required()
			    ->unique(ignoreRecord: true)
			    ->maxLength(255),

			Forms\Components\Toggle::make('is_active')
			    ->label('Activa')
			    ->default(true),
		    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
	return $table
	    ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
		Tables\Columns\TextColumn::make('city')
		     ->sortable()
                     ->badge()
		     ->color('gray'),

                Tables\Columns\TextColumn::make('name')
		    ->label('Colonia')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColonies::route('/'),
            'create' => Pages\CreateColony::route('/create'),
            'edit' => Pages\EditColony::route('/{record}/edit'),
        ];
    }
}
