<?php
    ini_set('memory_limit', '768M');
    require('lib/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    $site_root = 'C:/Users/Tekton/Documents/isaiahexplained_code/';
    $concordance_html = $site_root . 'Extraction/doc/Concordance.html';
    //$concordance_html = $site_root . 'Extraction/doc/Concordance_Sample.html';

    $html = file_get_html($concordance_html);

    $segment_id = 1;

    foreach($html->find('p') as $concordanceHTML) {
        $class = $concordanceHTML->attr['class'];
        if (empty($amysql)) {
            $amysql = get_amysql();
        }
        if($class == 'WRD') {
            $word = $concordanceHTML->innertext;
            $letter = strtoupper($word[0]);
            $concordance_id = insert_concordance($word, $letter, $amysql);
            $segment_id = 1;
        } elseif($class = 'CIT') {
            $citation = $concordanceHTML->innertext;
            $is_match = preg_match('/^(\d{1,2}):(\d{1,2})/', $citation, $matches);
            if($is_match) {
                if(!empty($chapter_number) && !empty($verse_number)) {
                    if ($chapter_number == $matches[1] && $verse_number == $matches[2]) {
                        $segment_id++;
                    } else {
                        $segment_id = 1;
                    }
                }
                $citation = preg_replace('/\d{1,2}:\d{1,2}\s/', '', $citation);
                //http://isaiahexplained.com/Concordance/chapter44.html?h=c44v26-JUDAH#c44v26
                $subject_verse = $matches[0];
                $chapter_number = $matches[1];
                $verse_number = $matches[2];
                $chapter_id = get_book_chapter_id('Isaiah IIT', $chapter_number, $amysql);
                $verse_id = get_book_verse_id('Isaiah IIT', $chapter_number, $verse_number, $amysql);
                $segment_letter = toAlpha($segment_id, 1);
                $url = "c${chapter_number}v${verse_number}${segment_letter}-${word}";
                $citation_id = insert_concordance_citation(
                    $concordance_id, $chapter_id, $verse_id, $chapter_number, $url,
                    $subject_verse, $letter,  $citation, $segment_id, $amysql);
            } else {
                $test = 1;
            }
            $test = 1;
        } else {
            $test = 1;
        }
    }

function toAlpha($data, $offset = 0){
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