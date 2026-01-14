<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyProfileResource\Pages;
use App\Filament\Resources\FamilyProfileResource\RelationManagers;
use App\Models\FamilyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class FamilyProfileResource extends Resource
{
    protected static ?string $model = FamilyProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Casos Familiares';
    protected static ?string $label = 'Perfil Familiar';
    protected static ?string $pluralLabel = 'Perfiles Familiares';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Forms\Components\FileUpload::make('family_photo_path')
                                    ->image()
                                    ->directory('families')
                                    ->columnSpan(4),
                                Grid::make(12)
                                    ->schema([
                                        Forms\Components\TextInput::make('family_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(6),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->columnSpan(6),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'prospect' => 'Prospecto',
                                                'active' => 'Activo',
                                                'in_follow_up' => 'En seguimiento',
                                                'closed' => 'Cerrado',
                                            ])
                                            ->required()
                                            ->columnSpan(6),
                                        Forms\Components\Select::make('responsible_member_id')
                                            ->relationship('responsibleMember', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label('Responsable')
                                            ->columnSpan(6),
                                        Forms\Components\DatePicker::make('opened_at')
                                            ->required()
                                            ->columnSpan(6),
                                        Forms\Components\DatePicker::make('closed_at')
                                            ->columnSpan(6),
                                    ])
                                    ->columnSpan(8),
                            ]),
                    ]),
                Section::make('Direcciones')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Forms\Components\TextInput::make('current_address')
                                    ->columnSpan(6),
                                Forms\Components\TextInput::make('current_address_link')
                                    ->url()
                                    ->columnSpan(6),
                                Forms\Components\TextInput::make('construction_address')
                                    ->columnSpan(6),
                                Forms\Components\TextInput::make('construction_address_link')
                                    ->url()
                                    ->columnSpan(6),
                            ]),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('general_observations')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsibleMember.full_name')
                    ->label('Responsable'),

                Tables\Columns\TextColumn::make('opened_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('closed_at')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'prospect' => 'Prospecto',
                        'active' => 'Activo',
                        'in_follow_up' => 'En seguimiento',
                        'closed' => 'Cerrado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\MembersRelationManager::class,
            RelationManagers\VisitsRelationManager::class,
            RelationManagers\TestimoniesRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            //RelationManagers\NotesRelationManager::class,
            //RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyProfiles::route('/'),
            'create' => Pages\CreateFamilyProfile::route('/create'),
            'edit' => Pages\EditFamilyProfile::route('/{record}/edit'),
        ];
    }
}
