<?php

namespace Modules\Circulation\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Listeners\RecordBookBorrowed;

class CirculationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Event::listen(BookBorrowed::class, RecordBookBorrowed::class);
    }
}
