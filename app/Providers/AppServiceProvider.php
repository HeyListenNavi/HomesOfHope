<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        FilamentAsset::register([
            Css::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            Js::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
            Css::make('leaflet-geoman', 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css'),
            Js::make('leaflet-geoman', 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js'),
            Css::make('boxicons', 'https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css'),
            Css::make('boxicons-filled', 'https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css'),
            Css::make('boxicons-brands', 'https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css'),

            Js::make('polygon-map-picker', asset('js/group-applicant-map.js')),
        ]);
    }
}
