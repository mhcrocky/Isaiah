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

    /**
     * Gets html for the chapter selection widget
     *
     * @param int $chapter Chapter number
     * @return array Chapter selection array for mustache template
     */
    public static function GetKJVChapterSelection($chapter, $chapter_count) {
        $chapter_selection = array();
        for($j = 0, $i = 1; $j < $chapter_count; $j++, $i++) {
            $chapter_selection[$j]['selection_number'] = $i;
            if($i == $chapter) {
                $chapter_selection[$j]['button_class'] = 'btn-warning';
            } else {
                $chapter_selection[$j]['button_class'] = 'btn-default';
            }
        }
        return $chapter_selection;
    }

    /**
     * Gets html for the concordance letter selection widget
     *
     * @param string $letter Concordance letter
     * @return array Chapter selection array for mustache template
     */
    public static function GetConcordanceSelection($letter = '') {
        $letter_selection = array();
        for($j = 0, $i = 1; $i <= 26; $i++, $j++) {
            $letter_selection[$j]['selection_letter'] = strtoupper(WidgetRepository::toAlpha($i, 1));
            if(!empty($letter) && strtoupper(WidgetRepository::toAlpha($i, 1)) == strtoupper($letter)) {
                $letter_selection[$j]['button_class'] = 'btn-warning';
            } else {
                $letter_selection[$j]['button_class'] = 'btn-default';
            }
        }
        return $letter_selection;
    }

    public static function toAlpha($data, $offset = 0){
        if(!empty($offset)) {
            $data--;
        }
        $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $alpha_flip = array_flip($alphabet);
        if($data <= 25){
            return $alphabet[$data];
        }
        elseif($data > 25){
            $dividend = ($data + 1);
            $alpha = '';
            while ($dividend > 0){
                $modulo = ($dividend - 1) % 26;
                $alpha = $alphabet[$modulo] . $alpha;
                $dividend = floor((($dividend - $modulo) / 26));
            }
            return $alpha;
        }

    }
} 