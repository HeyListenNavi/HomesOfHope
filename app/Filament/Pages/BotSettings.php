<?php

namespace App\Filament\Pages;

use App\Models\BotSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;

class BotSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.bot-settings';
    protected static ?string $navigationLabel = 'Configuración';
    protected static ?string $navigationGroup = 'Configuración';
    protected ?string $heading = 'Configuración';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = BotSetting::whereIn('name', ['ask_name', 'ask_curp', 'ask_gender'])
            ->pluck('value', 'name')
            ->toArray();

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Preguntas iniciales')
                    ->description('Configura las preguntas que enviará el bot para obtener los datos iniciales de un aplicante al comenzar su proceso de registro.')
                    ->aside()
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->schema([
                        Textarea::make('ask_name')
                            ->label('Pregunta por Nombre')
                            ->helperText('La respuesta se guardará en el campo "Nombre Completo" de la aplicación.')
                            ->placeholder('Ej: Para comenzar con tu registro, ¿podrías decirme tu nombre completo?')
                            ->autoSize()
                            ->required(),

                        Textarea::make('ask_curp')
                            ->label('Pregunta por CURP')
                            ->helperText('La respuesta se guardará en el campo "CURP" de la aplicación.')
                            ->placeholder('Ej: Gracias. Ahora, por favor ingresa tu CURP para verificar tu información.')
                            ->autoSize()
                            ->required(),

                        Textarea::make('ask_gender')
                            ->label('Pregunta por Género')
                            ->placeholder('Ej: Para completar tu perfil, ¿cuál es tu género?')
                            ->autoSize()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->icon('heroicon-m-check')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $name => $value) {
            BotSetting::where('name', $name)->update(['value' => $value]);
        }

        Notification::make()
            ->title('Configuración guardada')
            ->body('Las preguntas del bot se han actualizado correctamente.')
            ->success()
            ->send();
    }
}
