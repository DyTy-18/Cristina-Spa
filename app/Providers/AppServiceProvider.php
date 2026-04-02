<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            if (auth()->check()) {
                $view->with('alertasStockCount', \App\Models\AlertaStock::where('leida', false)->count());
            }
        });
    }
}
