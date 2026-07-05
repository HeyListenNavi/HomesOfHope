<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ApplicantResource;
use App\Filament\Resources\ColonyResource;
use App\Filament\Resources\ConversationResource;
use App\Filament\Resources\FamilyMemberResource;
use App\Filament\Resources\FamilyProfileResource;
use App\Filament\Resources\GroupResource;
use App\Filament\Resources\MessageResource;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\StageResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VisitResource;
use App\Filament\Widgets\ApplicantsList;
use App\Filament\Widgets\ApplicantsOverview;
use App\Filament\Widgets\MonthlyApplicantsChart;
use App\Filament\Widgets\TotalApplicantsChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#61b346'),
            ])
            ->resources([
                FamilyProfileResource::class,
                FamilyMemberResource::class,
                VisitResource::class,
                ApplicantResource::class,
                GroupResource::class,
                StageResource::class,
                QuestionResource::class,
                ColonyResource::class,
                UserResource::class,
                ConversationResource::class,
                MessageResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                ApplicantsOverview::class,
                MonthlyApplicantsChart::class,
                TotalApplicantsChart::class,
                ApplicantsList::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
