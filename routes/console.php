<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('images:cleanup')->everyFiveMinutes();
