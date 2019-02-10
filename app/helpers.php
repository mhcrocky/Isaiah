<?php
namespace Helpers;

use Carbon\Carbon;

/**
 * Get last week's date
 *
 * @return string
 */
function getLastWeek() {
    return Carbon::now()->subWeek();
}

/**
 * Get last month's date
 *
 * @return string
 */
function getLastMonth() {
    return Carbon::now()->subMonth();
}

/**
 * Get the meta tag for robots noindex,nofollow
 *
 * @param $query_string
 * @return string
 */
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

/**
 * Get references from URL
 *
 * @param $reference_input
 * @return array
 */
function getReferences($reference_input)
{
    $references = [];

    $is_error = false;

    $verse_limit = 177;

    if (!empty($reference_input)) {
        if (preg_match_all('/(\d+-\d+|\d+)|,(41\.7)|,(\d+-\d+|\d+)/', $reference_input, $matches)) {
            if (!empty($matches)) {
                foreach ($matches[0] as $reference_match) {
                    $fixed_reference = ltrim($reference_match, ',');
                    if (strpos($fixed_reference, '-') !== false) {
                        $parts = explode('-', $fixed_reference);
                        if (!empty($parts[0]) && !empty($parts[1])) {
                            $start = $parts[0];
                            if ($start > 0 && $start < $verse_limit) {
                                $end = $parts[1];
                                if ($end > $start && $end < $verse_limit) {
                                    for ($i = $start; $i <= $end; $i++) {
                                        if (!in_array($i, $references)) {
                                            $references[] = "{$i}";
                                        }
                                    }
                                } else {
                                    $is_error = true;
                                }
                            } else {
                                $is_error = true;
                            }
                        }
                    } else {
                        if (!in_array($fixed_reference, $references)) {
                            $references[] = $fixed_reference;
                        }
                    }
                }
            }
        }
    }


    if ($is_error == true) {
        App::abort(403, 'Unauthorized action.');
    } else {
        asort($references);
        return $references;
    }
}