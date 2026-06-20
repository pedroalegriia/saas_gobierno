<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('capture-lines:expire')->hourly();
