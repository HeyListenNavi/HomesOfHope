<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Enums\TagType;
use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageTags extends ManageRecords
{
    protected static string $resource = TagResource::class;

    public function getDefaultActiveTab(): string|int|null
    {
        return 'applicants';
    }

    public function getTabs(): array
    {
        return [
            'applicants' => Tab::make('Aplicantes')
                ->icon('heroicon-m-user')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', TagType::Applicant)),
            'family_profiles' => Tab::make('Familias')
                ->icon('heroicon-m-home')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', TagType::FamilyProfile)),
            'all' => Tab::make('Todos')
                ->icon('heroicon-m-tag'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('md')
                ->fillForm(fn (): array => [
                    'type' => match ($this->activeTab) {
                        'family_profiles' => TagType::FamilyProfile,
                        default => TagType::Applicant,
                    },
                ]),
        ];
    }
}
