<?php

class WidgetRepository {
    /**
     * Gets html for the chapter selection widget
     *
     * @param int $chapter Chapter number
     * @return array Chapter selection array for mustache template
     */
    public static function GetChapterSelection($chapter) {
        $chapter_selection = array();
        for($j = 0, $i = 1; $i <= 66; $i++, $j++) {
            $chapter_selection[$j]['selection_number'] = $i;
            if($i == $chapter) {
                $chapter_selection[$j]['button_class'] = 'btn-warning';
            } else {
                $chapter_selection[$j]['button_class'] = 'btn-default';
            }
        }
        return $chapter_selection;
    }
} 