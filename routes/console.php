<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Borra respaldos de registro_envios más antiguos que RegistroEnvio::DIAS_RETENCION
Schedule::command('model:prune', ['--model' => [\App\Models\RegistroEnvio::class]])->daily();
