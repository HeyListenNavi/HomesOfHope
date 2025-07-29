<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Message; // Asegúrate de que el modelo Message está importado
use App\Models\Conversation; // Importa el modelo Conversation

class ApplicantMessagesRelationManager extends RelationManager
{
    // --- CAMBIO CLAVE AQUÍ ---
    // Apuntamos la relación a 'conversation'. Filament la usará para inicialización.
    protected static string $relationship = 'conversation';
    // --- FIN DEL CAMBIO CLAVE ---

    // El modelo del Relation Manager sigue siendo Message
    protected static ?string $model = Message::class;

    // El título de la pestaña
    protected static ?string $title = 'Mensajes de la Conversación';

    // Este método es crucial para obtener los mensajes CORRECTOS.
    public function getTableQuery(): Builder
    {
        // Obtenemos el registro de Applicant actual.
        $applicant = $this->ownerRecord;

        // Si el applicant tiene una conversación vinculada...
        if ($applicant->conversation) {
            // Retorna los mensajes de ESA conversación.
            return $applicant->conversation->messages()->getQuery();
        }

        // Si el applicant no tiene una conversación, retorna una query vacía.
        // Esto evita errores y asegura que no se muestren mensajes si no hay conversación.
        return Message::query()->whereRaw('1 = 0');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options([
                        'applicant' => 'Solicitante',
                        'admin' => 'Administrador',
                    ])
                    ->required()
                    ->native(false),

                // El conversation_id se pre-rellena automáticamente si el Applicant ya tiene una conversación.
                // Es un campo oculto porque no queremos que el usuario lo cambie manualmente para un mensaje de este Applicant.
                Forms\Components\Hidden::make('conversation_id')
                    ->default(fn () => $this->ownerRecord->conversation->id ?? null),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->message)
                    ->wrap()
                    ->description(fn ($record) => $record->created_at->diffForHumans(), position: 'below'),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rol')
                    ->colors([
                        'primary' => 'applicant',
                        'success' => 'admin',
                    ]),

                Tables\Columns\TextColumn::make('conversation.chat_id') // Muestra el ID de la conversación
                    ->label('ID Conversación')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'applicant' => 'Solicitante',
                        'admin' => 'Administrador',
                    ])
                    ->label('Filtrar por Rol'),
                // No hay un filtro de conversación aquí, ya que solo estamos mostrando los mensajes
                // de LA conversación de este solicitante. Si quisieras más opciones, la lógica sería más compleja.
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // Solo permite crear si el Applicant ya tiene una conversación asignada
                    ->visible(fn () => $this->ownerRecord->conversation !== null)
                    ->mutateFormDataUsing(function (array $data): array {
                        // Asigna automáticamente el ID de la conversación del Applicant al nuevo mensaje
                        $data['conversation_id'] = $this->ownerRecord->conversation->id;
                        return $data;
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
            ]);
    }
}
