<?php
    ini_set('memory_limit', '512M');
    require('lib/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    $site_root = 'C:/Users/thetekton/Documents/isaiahexplained/';
    $segment_id = 1;
    $book_title = 'Isaiah IIT';
    
    $message = 'success';
                                
    $color_keywords_html = $site_root . 'Extraction/doc/Key Words in Context.html';
    $html = file_get_html($color_keywords_html);
    foreach($html->find('p[class=western]') as $iitHTML) {
        $iitInnerHTML = $iitHTML->innertext;
        // Filtering
        $is_color_matched = preg_match('/color="(.*)"/U', $iitInnerHTML, $matches);
        if($is_color_matched = true) {
            if(empty($matches[1])) {
                continue;
            }
            $raw_color = $matches[1];
            
            switch($raw_color) {
                case '#c00000':
                    $color['name'] = 'red';
                    break;
                case '#0070c0':
                    $color['name'] = 'blue';
                    break;
                case '#948a54':
                    $color['name'] = 'tan';
                    break;
                case '#7030a0':
                    $color['name'] = 'purple';
                    break;
                case '#00b050':
                    $color['name'] = 'green';
                    break;
                default:
                    continue(2);
                    break;
            }
            $color['value'] = ltrim($raw_color, '#');
        } else {
            $message = 'Error: Color not matched';
            break;
        }
        
        $section_text = strip_tags($iitInnerHTML, '<strong><em><b><i><sup><br>');
        $section_text = preg_replace('/\r\n/', ' ', $section_text);
        $section_text = preg_replace('/\t+/', '', $section_text);
        $is_matched = preg_match('/(\d+):(\d+)—(\D+)—(.*)/', $section_text, $matches, PREG_OFFSET_CAPTURE);
        if($is_matched == true) {
            $chapter = $matches[1][0];
            $verse = $matches[2][0];
            $keyword = $matches[3][0];
            $keyword_description = $matches[4][0];

            try {
                if(empty($amysql)) {
                    $amysql = get_amysql();
                }
                $verse_id = get_book_verse_id($book_title, $chapter, $verse, $amysql);
                insert_verse_keyword($verse_id, $color['name'], $color['value'], strip_tags($keyword), htmlentities($keyword_description), $amysql);
                //sleep(1);
            } catch(Exception $ex) {
                $test = 1;
            }
        } else {
            continue;
        }
                                                                                          
        //$test = 1;
    }
    
    echo $message;
?>