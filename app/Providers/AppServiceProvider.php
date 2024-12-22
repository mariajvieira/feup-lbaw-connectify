<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View; 
use Illuminate\Database\Eloquent\Relations\Relation;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(env('FORCE_HTTPS',false)) {
            error_log('configuring https');

            $app_url = config("app.url");
            URL::forceRootUrl($app_url);
            $schema = explode(':', $app_url)[0];
            URL::forceScheme($schema);
        }
        Relation::morphMap([
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
        ]);


        View::composer(['layouts.app'], function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $groupsAsMember = $user->groups;
                $ownedGroups = $user->ownedGroups;
                $allGroups = $groupsAsMember->merge($ownedGroups);
                $view->with('allGroups', $allGroups);
            } else {
                $view->with('allGroups', collect()); // Caso não esteja autenticado, usa uma coleção vazia
            }

        });
    }
}
