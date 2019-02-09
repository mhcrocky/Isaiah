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

/**
 * Strip a string from the end of a string
 *
 * @param mixed $message the input string
 * @param mixed $strip string to remove
 *
 * @return string the modified string
 */
function strrtrim($message, $strip) {
    $lines = explode($strip, $message);
    $last  = '';
    do {
        $last = array_pop($lines);
    } while (empty($last) && (count($lines)));
    return implode($strip, array_merge($lines, array($last)));
}