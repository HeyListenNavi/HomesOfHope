<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartialApplicantResource\Pages;
use App\Filament\Resources\PartialApplicantResource\RelationManagers;
use App\Models\PartialApplicant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartialApplicantResource extends Resource
{
    protected static ?string $model = PartialApplicant::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Auditoría y Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('conversation_id')
                    ->relationship('conversation', 'chat_id')
                    ->required()
                    ->readOnly(),
                Forms\Components\TextInput::make('current_evaluation_status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\KeyValue::make('evaluation_data')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_completed')
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Estado del Proceso de Evaluación')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('conversation.chat_id')
                            ->label('Chat ID'),
                        Components\TextEntry::make('current_evaluation_status')
                            ->label('Estado Actual')
                            ->badge()
                            ->color('warning'),
                        Components\IconEntry::make('is_completed')
                            ->label('Completado')
                            ->boolean()
                            ->icon(fn (bool $state): string => match ($state) {
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            })
                            ->color(fn (bool $state): string => match ($state) {
                                true => 'success',
                                false => 'danger',
                            }),
                    ]),
                Components\Section::make('Datos Recopilados')
                    ->schema([
                        Components\KeyValueEntry::make('evaluation_data')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conversation.chat_id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_evaluation_status')
                    ->searchable()
                    ->badge(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Evaluaciones Completas')
                    ->nullable()
                    ->trueLabel('Completadas')
                    ->falseLabel('Incompletas')
                    ->placeholder('Todas'),
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPartialApplicants::route('/'),
            //'create' => Pages\CreatePartialApplicant::route('/create'),
            //'view' => Pages\ViewPartialApplicant::route('/{record}'),
            //'edit' => Pages\EditPartialApplicant::route('/{record}/edit'),
        ];
    }
}
