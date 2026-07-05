<?php

use App\Jobs\FetchPricesJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new FetchPricesJob)->everyTenMinutes();
Schedule::command('alerts:check')->everyFifteenMinutes();
