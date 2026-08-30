<?php

namespace App\Providers;

use App\Models\Applicant;
use App\Models\BotSetting;
use App\Models\Colony;
use App\Models\Conversation;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\Group;
use App\Models\Message;
use App\Models\Question;
use App\Models\Stage;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use App\Policies\ApplicantPolicy;
use App\Policies\BotSettingPolicy;
use App\Policies\ColonyPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\FamilyMemberPolicy;
use App\Policies\FamilyProfilePolicy;
use App\Policies\GroupPolicy;
use App\Policies\MessagePolicy;
use App\Policies\QuestionPolicy;
use App\Policies\RolePolicy;
use App\Policies\StagePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitPolicy;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Applicant::class, ApplicantPolicy::class);
        Gate::policy(BotSetting::class, BotSettingPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Stage::class, StagePolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(Colony::class, ColonyPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(FamilyProfile::class, FamilyProfilePolicy::class);
        Gate::policy(FamilyMember::class, FamilyMemberPolicy::class);
        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        FilamentAsset::register([
            Css::make('boxicons', 'https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css'),
            Css::make('boxicons-filled', 'https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css'),
            Css::make('boxicons-brands', 'https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css'),

            Js::make('polygon-map-picker', asset('js/group-applicant-map.js')),
            Js::make('visit-map', asset('js/visit-map.js')),
            Js::make('dropdown-date-picker', asset('js/dropdown-date-picker.js')),

            Css::make('leaflet-cluster', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css'),
            Css::make('leaflet-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css'),
        ]);
    }
}
