<?php

namespace App\Filament\Pages;

use App\Enums\FamilyStatus;
use App\Enums\VisitLocationType;
use App\Enums\VisitStatus;
use App\Filament\Resources\FamilyProfileResource;
use App\Models\FamilyProfile;
use App\Models\User;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class MapVisitsPage extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Familias';

    protected static string $view = 'filament.pages.map-visits-page';

    protected static ?string $title = 'Mapa de Visitas';

    public ?string $search = '';

    public ?string $status = null;

    public ?int $month = null;

    public ?int $year = null;

    public array $selectedLocations = [];

    public ?array $formData = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'status', 'month', 'year'])) {
            $this->dispatch('sync-map-points', markers: $this->markers);
        }
    }

    #[Computed]
    public function markers(): array
    {
        $query = FamilyProfile::query()
            ->when($this->search, function (Builder $q) {
                $q->where('family_name', 'like', "%{$this->search}%")
                    ->orWhereHas('members', function (Builder $q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('paternal_surname', 'like', "%{$this->search}%")
                            ->orWhere('maternal_surname', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->status, fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->month, fn (Builder $q) => $q->whereMonth('opened_at', $this->month))
            ->when($this->year, fn (Builder $q) => $q->whereYear('opened_at', $this->year));

        $families = $query->get();
        $markers = [];

        foreach ($families as $family) {
            if ($family->home_latitude && $family->home_longitude) {
                $markers[] = [
                    'id' => 'home_'.$family->id,
                    'family_id' => $family->id,
                    'family_name' => $family->family_name,
                    'type' => 'home',
                    'title' => $family->family_name.' (Casa)',
                    'lat' => (float) $family->home_latitude,
                    'lng' => (float) $family->home_longitude,
                ];
            }

            if ($family->land_latitude && $family->land_longitude) {
                $markers[] = [
                    'id' => 'land_'.$family->id,
                    'family_id' => $family->id,
                    'family_name' => $family->family_name,
                    'type' => 'land',
                    'title' => $family->family_name.' (Terreno)',
                    'lat' => (float) $family->land_latitude,
                    'lng' => (float) $family->land_longitude,
                ];
            }
        }

        return $markers;
    }

    protected function getForms(): array
    {
        return [
            'form',
            'filtersForm',
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->hiddenLabel()
                    ->placeholder('Buscar familia por nombre o miembros...')
                    ->live(debounce: 500),

                Select::make('status')
                    ->hiddenLabel()
                    ->placeholder('Todos los Estados')
                    ->options(FamilyStatus::class)
                    ->native(false)
                    ->live(),

                Select::make('month')
                    ->hiddenLabel()
                    ->placeholder('Cualquier Mes')
                    ->options([
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ])
                    ->native(false)
                    ->live(),

                TextInput::make('year')
                    ->hiddenLabel()
                    ->placeholder('Cualquier Año')
                    ->numeric()
                    ->live(debounce: 500),
            ])
            ->columns(['md' => 4])
            ->statePath('');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_ids')
                    ->label('Asignar a Usuarios')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->multiple(),

                DatePicker::make('scheduled_at')
                    ->label('Fecha de Visita')
                    ->placeholder('Fecha programada')
                    ->required()
                    ->native(false),
            ])
            ->statePath('formData');
    }

    public function finalizeAction(): Action
    {
        return Action::make('finalize')
            ->label('Agendar Visitas')
            ->icon('heroicon-o-check-circle')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Confirmar Visitas')
            ->modalDescription('¿Estás seguro de que deseas agendar estas visitas seleccionadas?')
            ->modalSubmitActionLabel('Sí, Agendar')
            ->action(function () {
                $data = $this->form->getState();

                if (empty($this->selectedLocations)) {
                    Notification::make()
                        ->title('Error')
                        ->body('No has seleccionado ninguna ubicación.')
                        ->danger()
                        ->send();

                    return;
                }

                DB::transaction(function () use ($data) {
                    foreach ($this->selectedLocations as $loc) {
                        $visit = Visit::create([
                            'family_profile_id' => $loc['family_id'],
                            'location_type' => $loc['type'] === 'home' ? VisitLocationType::Home : VisitLocationType::Land,
                            'status' => VisitStatus::Pending,
                            'scheduled_at' => $data['scheduled_at'] ?? null,
                        ]);

                        if (! empty($data['user_ids'])) {
                            $visit->attendants()->attach($data['user_ids']);
                        }
                    }
                });

                $this->selectedLocations = [];
                $this->form->fill();
                $this->dispatch('sync-selected-locations');

                Notification::make()
                    ->title('Éxito')
                    ->body('Visitas agendadas correctamente.')
                    ->success()
                    ->send();
            });
    }

    public function toggleLocation($familyId, $type)
    {
        $index = null;
        foreach ($this->selectedLocations as $key => $loc) {
            if ($loc['family_id'] === $familyId && $loc['type'] === $type) {
                $index = $key;
                break;
            }
        }

        if ($index !== null) {
            unset($this->selectedLocations[$index]);
            $this->selectedLocations = array_values($this->selectedLocations);
        } else {
            if (count($this->selectedLocations) >= 10) {
                Notification::make()
                    ->title('Límite alcanzado')
                    ->body('Máximo 10 ubicaciones por ruta de visitas.')
                    ->warning()
                    ->send();

                return;
            }
            $this->selectedLocations[] = [
                'family_id' => $familyId,
                'type' => $type,
            ];
        }

        $this->dispatch('sync-selected-locations');
    }

    public function removeLocation($familyId, $type)
    {
        $this->selectedLocations = array_values(array_filter($this->selectedLocations, function ($loc) use ($familyId, $type) {
            return ! ($loc['family_id'] === $familyId && $loc['type'] === $type);
        }));

        $this->dispatch('sync-selected-locations');
    }

    #[Computed]
    public function selectedDetails()
    {
        $details = [];
        foreach ($this->markers as $marker) {
            $isSelected = false;
            foreach ($this->selectedLocations as $loc) {
                if ($loc['family_id'] === $marker['family_id'] && $loc['type'] === $marker['type']) {
                    $isSelected = true;
                    break;
                }
            }

            if ($isSelected) {
                $details[] = [
                    'family_id' => $marker['family_id'],
                    'family_name' => $marker['family_name'],
                    'type' => $marker['type'],
                    'label' => $marker['type'] === 'home' ? 'Casa' : 'Terreno',
                    'view_url' => FamilyProfileResource::getUrl('view', ['record' => $marker['family_id']]),
                ];
            }
        }

        return $details;
    }
}
