<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ApplicantQuestionResponseRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    protected static ?string $title = 'Respuestas';

    protected static ?string $icon = 'heroicon-m-chat-bubble-left-right';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contenido de la Respuesta')
                    ->description('Revisión de la pregunta realizada y la respuesta capturada.')
                    ->icon('heroicon-m-document-text')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Textarea::make('question_text_snapshot')
                            ->label('Pregunta Realizada')
                            ->disabled()
                            ->readonly()
                            ->autosize(),

                        Forms\Components\Textarea::make('user_response')
                            ->label('Respuesta del Usuario')
                            ->rows(4)
                            ->autosize()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Análisis de Inteligencia Artificial')
                    ->description('Validación automática y razonamiento.')
                    ->icon('heroicon-m-cpu-chip')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('ai_decision')
                            ->label('Decisión de la IA')
                            ->options([
                                'valid' => 'Válido',
                                'not_valid' => 'No Válido',
                                'requires_supervision' => 'Requiere Supervisión',
                            ])
                            ->prefixIcon('heroicon-m-scale'),

                        Forms\Components\Textarea::make('ai_decision_reason')
                            ->label('Razonamiento de la IA')
                            ->rows(3)
                            ->autosize()
                            ->visible(fn (Get $get) => in_array($get('ai_decision'), ['not_valid', 'requires_supervision']))
                            ->helperText('Explica por qué la IA marcó esta respuesta como inválida o dudosa.'),
                    ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function canEdit(Model $record): bool
    {
        return true;
    }

    public function canDelete(Model $record): bool
    {
        return false;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text_snapshot')
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('question.stage.name')
            ->columns([
                TextColumn::make('question.order')
                    ->label('Orden')
                    ->hidden()
                    ->sortable(),

                IconColumn::make('ai_decision')
                    ->label('IA')
                    ->color(fn (string $state): string => match ($state) {
                        'valid' => 'success',
                        'requires_supervision' => 'warning',
                        'not_valid' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'valid' => 'heroicon-m-check-circle',
                        'requires_supervision' => 'heroicon-m-eye',
                        'not_valid' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-minus',
                    })
                    ->size(IconColumn\IconColumnSize::Small)
                    ->wrap()
                    ->sortable(),

                TextColumn::make('question_text_snapshot')
                    ->label('Pregunta')
                    ->color('gray')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('user_response')
                    ->label('Respuesta')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->searchable()
                    ->formatStateUsing(fn (?string $state) => self::extractLocationUrl($state) ? '📍 Ver en Mapa' : str($state)->limit(90))
                    ->color(fn (?string $state) => self::extractLocationUrl($state) ? 'primary' : null)
                    ->url(fn (?string $state) => self::extractLocationUrl($state))
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ai_decision')
                    ->label('Filtrar por Decisión')
                    ->options([
                        'valid' => 'Válido',
                        'not_valid' => 'No Válido',
                        'requires_supervision' => 'Requiere Supervisión',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Respuesta Manual'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading(''),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function extractLocationUrl(?string $state): ?string
    {
        if (empty($state)) {
            return null;
        }

        $cleanState = trim($state);

        // Notas de Vero real:

        // Match a un link de maps
        if (preg_match('/(https?:\/\/(www\.)?google\.[a-z.]+\/maps\/[^\s]+|https?:\/\/goo\.gl\/maps\/[^\s]+|https?:\/\/maps\.app\.goo\.gl\/[^\s]+)/i', $cleanState, $matches)) {
            return $matches[0];
        }

        // Match a coordenadas
        if (preg_match('/(?<![\d.\-+])[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)(?![\d.])/', $cleanState, $matches)) {
            return 'https://maps.google.com/?q='.urlencode(trim($matches[0]));
        }

        // Match a Plus Code
        if (preg_match('/([23456789C][23456789CFGHJMPQRV][23456789CFGHJMPQRVWX]{6}\+[23456789CFGHJMPQRVWX]{2,7}|[23456789CFGHJMPQRVWX]{4,6}\+[23456789CFGHJMPQRVWX]{2,3})/i', $cleanState, $matches)) {
            return 'https://maps.google.com/?q='.urlencode(trim($matches[0]));
        }

        return null;
    }
}
