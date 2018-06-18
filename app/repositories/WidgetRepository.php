<?php

class WidgetRepository {
    /**
     * Gets html for the chapter selection widget
     *
     * @param int $chapter Chapter number
     * @return string Chapter selection widget html
     */
    public static function GetChapterSelection($chapter) {
        $chapter_selection = "\t\t<ul class=\"chapter-selection\">\r\n";
        for($i = 1; $i <= 66; $i++) {
            $btn_class = 'btn-default';
            if($i == $chapter) {
                $btn_class = 'btn-warning';
            }
            $chapter_selection .= "\t\t\t<li><a class=\"btn ${btn_class}\" href=\"/${i}\">${i}</a></li>\r\n";
        }
        $chapter_selection .= "\t\t</ul>";

        return $chapter_selection;
    }
} 