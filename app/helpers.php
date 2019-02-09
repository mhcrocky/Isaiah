<?php
namespace Helpers;

use Carbon\Carbon;

function getLastWeek() {
    return Carbon::now()->subWeek();
}

function getLastMonth() {
    return Carbon::now()->subMonth();
}

function getRobotIgnoreMeta($query_string) {
    if(!empty($query_string)) {
        return <<<EOT
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
EOT;
    } else {
        return '';
    }
}