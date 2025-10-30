<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule database backups daily at 2 AM
Schedule::command('db:backup')->dailyAt('02:00')->onOneServer();

// Schedule cache cleanup weekly
Schedule::command('cache:prune-stale-tags')->weekly()->onOneServer();

// Schedule queue monitoring
Schedule::command('queue:monitor database --max=100')->everyFiveMinutes();

// Schedule log cleanup monthly
Schedule::command('log:clear')->monthly()->onOneServer();
