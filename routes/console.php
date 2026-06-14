<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('prices:fetch')->everyTenMinutes();
Schedule::command('alerts:check')->everyFifteenMinutes();
