<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        /*** Header File ***/
        View::composer('layouts.topbar', static function (\Illuminate\View\View $view) {
            $view->with('languages', Language::get());
        });

        View::composer('layouts.sidebar', static function (\Illuminate\View\View $view) {
            $settings = Setting::where('name', 'company_logo')->first();
            $view->with('company_logo', $settings->value ?? '');
        });

        View::composer('layouts.main', static function (\Illuminate\View\View $view) {
            $settings = Setting::where('name', 'favicon_icon')->first();
            $view->with('favicon', $settings->value ?? '');
            $view->with('lang', Session::get('language'));
        });

        View::composer('auth.login', static function (\Illuminate\View\View $view) {
            $settings = Setting::where('name', 'favicon_icon')
                ->orWhere('name', 'company_logo')
                ->orWhere('name', 'login_image')
                ->get()->pluck('value', 'name');
            $view->with('company_logo', $settings['company_logo'] ?? '');
            $view->with('favicon', $settings['favicon_icon'] ?? '');
            $view->with('login_bg_image', $settings['login_image'] ?? '');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        //
    }
}
