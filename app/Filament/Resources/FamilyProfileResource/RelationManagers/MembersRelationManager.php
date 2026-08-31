<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use App\Enums\EducationLevel;
use App\Enums\IndigenousLanguage;
use App\Enums\MaritalStatus;
use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\Religion;
use App\Filament\Forms\Components\DropdownDatePicker;
use App\Filament\Resources\FamilyMemberResource;
use App\Filament\Resources\FamilyProfileResource;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Miembros';

    protected static ?string $icon = 'heroicon-s-user-group'; // Icono sólido para la pestaña

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // SECCIÓN 1: IDENTIDAD
                        Forms\Components\Section::make('Identificación Personal')
                            ->icon('heroicon-s-identification')
                            ->schema([
                                // Fila de Nombres
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre(s)')
                                            ->required()
                                            ->placeholder('Ej. Juan Carlos')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('paternal_surname')
                                            ->label('Apellido Paterno')
                                            ->required()
                                            ->placeholder('Ej. Pérez')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('maternal_surname')
                                            ->label('Apellido Materno')
                                            ->placeholder('Ej. López')
                                            ->maxLength(255),
                                    ]),

                                // Fila de Detalles
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        DropdownDatePicker::make('birth_date')
                                            ->label('Fecha de Nacimiento')
                                            ->maxYear(now()->year)
                                            ->showAge()
                                            ->required(),

                                        Forms\Components\TextInput::make('curp')
                                            ->label('CURP')
                                            ->maxLength(18)
                                            ->prefixIcon('heroicon-s-finger-print')
                                            ->placeholder('CURP')
                                            ->formatStateUsing(fn (?string $state) => strtoupper($state))
                                            ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper(trim($state)) : null)
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true)
                                            ->validationMessages([
                                                'unique' => 'Este CURP ya está registrado.',
                                            ])
                                            ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                            ->helperText(function (Forms\Components\TextInput $component, $livewire, Forms\Get $get, ?Model $record): ?HtmlString {
                                                if (! $livewire->getErrorBag()->has($component->getStatePath())) {
                                                    return null;
                                                }

                                                $curp = $get('curp');
                                                if (blank($curp)) {
                                                    return null;
                                                }

                                                $curp = strtoupper(trim($curp));
                                                $query = FamilyMember::where('curp', $curp);

                                                if ($record && $record->exists) {
                                                    $query->where('id', '!=', $record->id);
                                                }

                                                $existing = $query->first();

                                                if (! $existing) {
                                                    return null;
                                                }

                                                $url = FamilyMemberResource::getUrl('edit', ['record' => $existing]);
                                                $label = e($existing->full_name ?: $existing->name).' ↗';

                                                return new HtmlString(
                                                    '<a href="'.$url.'" target="_blank" class="underline font-bold text-primary-600 hover:text-primary-500 dark:text-primary-400 inline-flex items-center gap-0.5 text-xs">'
                                                    .$label
                                                    .'</a>'
                                                );
                                            }),

                                        Forms\Components\Select::make('occupation')
                                            ->label('Ocupación')
                                            ->options(Occupation::class)
                                            ->columnSpanFull()
                                            ->searchable()
                                            ->native(false)
                                            ->prefixIcon('heroicon-s-briefcase'),

                                        Forms\Components\TextInput::make('origin_country')
                                            ->label('País de Origen')
                                            ->placeholder('Ej. México')
                                            ->prefixIcon('heroicon-s-globe-alt'),

                                        Forms\Components\TextInput::make('origin_state')
                                            ->label('Estado de Origen')
                                            ->placeholder('Ej. Oaxaca, Chiapas, Puebla...')
                                            ->prefixIcon('heroicon-s-map-pin'),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Información Socioeconómica')
                            ->icon('heroicon-s-banknotes')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('marital_status')
                                    ->label('Estado Civil')
                                    ->options(MaritalStatus::class)
                                    ->native(false),

                                Forms\Components\TextInput::make('weekly_income')
                                    ->label('Ingreso Semanal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('0.00'),

                                Forms\Components\Select::make('education_level')
                                    ->label('Nivel de Estudios')
                                    ->options(EducationLevel::class)
                                    ->native(false),

                                Forms\Components\TextInput::make('education_grade')
                                    ->label('Grado')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(12),

                                Forms\Components\Select::make('religion')
                                    ->label('Religión')
                                    ->options(Religion::class)
                                    ->native(false)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Cultura y Lenguaje')
                            ->icon('heroicon-s-language')
                            ->schema([
                                Forms\Components\Checkbox::make('speaks_indigenous_language')
                                    ->label('Habla alguna lengua indígena')
                                    ->live(),

                                Forms\Components\Select::make('indigenous_language')
                                    ->label('¿Qué lengua indígena?')
                                    ->options(IndigenousLanguage::class)
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Selecciona una lengua')
                                    ->visible(fn (Forms\Get $get) => $get('speaks_indigenous_language'))
                                    ->required(fn (Forms\Get $get) => $get('speaks_indigenous_language')),
                            ]),

                        Forms\Components\Section::make('Ficha Médica')
                            ->description('Condiciones, alergias o notas de salud importantes.')
                            ->icon('heroicon-s-heart')
                            ->collapsed()
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Checkbox::make('is_pregnant')
                                            ->label('Está embarazada')
                                            ->live(),

                                        Forms\Components\TextInput::make('pregnancy_months')
                                            ->label('Meses de embarazo')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(9)
                                            ->visible(fn (Forms\Get $get) => $get('is_pregnant'))
                                            ->required(fn (Forms\Get $get) => $get('is_pregnant')),
                                    ])
                                    ->visible(fn (Forms\Get $get) => $get('relationship') === Relationship::Mother->value),

                                Forms\Components\Textarea::make('medical_notes')
                                    ->label('')
                                    ->rows(5)
                                    ->autoSize()
                                    ->placeholder('Escribe aquí alergias, enfermedades crónicas, tipo de sangre, etc.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Vinculación')
                            ->icon('heroicon-s-users')
                            ->schema([
                                Forms\Components\Select::make('relationship')
                                    ->label('Rol Familiar')
                                    ->options(Relationship::class)
                                    ->required()
                                    ->live()
                                    ->native(false),

                                Forms\Components\Toggle::make('is_responsible')
                                    ->label('Es el Aplicante')
                                    ->helperText('¿Es la persona que aplica?')
                                    ->onIcon('heroicon-s-check-badge')
                                    ->offIcon('heroicon-s-user')
                                    ->onColor('success'),

                                Forms\Components\Toggle::make('is_land_owner')
                                    ->label('Dueño del Terreno')
                                    ->helperText('¿Es el dueño legal del terreno?')
                                    ->onIcon('heroicon-s-map')
                                    ->offIcon('heroicon-o-map')
                                    ->onColor('info'),
                            ]),

                        Forms\Components\Section::make('Contacto Directo')
                            ->icon('heroicon-s-phone')
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono (WhatsApp)')
                                    ->prefixIcon('heroicon-s-chat-bubble-left-right'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->formatStateUsing(fn (FamilyMember $record) => "{$record->name} {$record->paternal_surname} {$record->maternal_surname}")
                    ->sortable()
                    ->description(fn (FamilyMember $record) => $record->is_land_owner ? '📍 Dueño del Terreno' : null)
                    ->icon(fn ($record) => $record->is_responsible ? 'heroicon-s-star' : null)
                    ->iconColor('warning'),

                Tables\Columns\TextColumn::make('familyProfile.family_name')
                    ->label('Familia')
                    ->sortable()
                    ->icon('heroicon-s-home')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('relationship')
                    ->label('Rol')
                    ->badge(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Edad')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? ($state->year === 1900 ? '-' : $state->age.' años') : '-')
                    ->description(fn (FamilyMember $record) => $record->birth_date ? $record->birth_date->format('d M Y') : null),

                // CAMBIO AQUÍ: Lógica de WhatsApp
                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->icon('heroicon-s-chat-bubble-left-right')
                    ->url(fn ($state) => $state ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $state) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('family_profile_id')
                    ->relationship('familyProfile', 'family_name')
                    ->getOptionLabelFromRecordUsing(fn (FamilyProfile $record) => Str::title($record->family_name))
                    ->label('Filtrar por Familia')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_responsible')
                    ->label('Solo Responsables'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->visible(fn (FamilyMember $record) => auth()->user()->can('update', $record))
                    ->slideOver(),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash')
                    ->visible(fn (FamilyMember $record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Miembro')
                    ->icon('heroicon-s-plus')
                    ->modalHeading('Registrar Miembro Familiar')
                    ->visible(fn () => auth()->user()->can('create', FamilyMember::class))
                    ->slideOver(),
            ])
            ->defaultSort('paternal_surname', 'asc');
    }
}
