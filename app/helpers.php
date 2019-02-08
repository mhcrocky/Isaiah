<?php
namespace Helpers;

use Carbon\Carbon;

function getLastWeek() {
    return Carbon::now()->subWeek();
}

function getLastMonth() {
    return Carbon::now()->subMonth();
}