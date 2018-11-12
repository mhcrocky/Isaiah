<?php
    ini_set('memory_limit', '512M');
    require('../../includes/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    $desktop = 'C:/Users/thetekton/Desktop/'; 
    $segment_id = 1;
    $book_title = 'Isaiah IIT';
    
    $message = 'success';
                                
    $color_keywords_html = $desktop . 'isaiahexplained.com/html/Color_Key_Words_in_Context.html';
    $html = file_get_html($color_keywords_html);
    foreach($html->find('p[class=western]') as $iitHTML) {
        $iitInnerHTML = $iitHTML->innertext;
        // Filtering
        $is_color_matched = preg_match('/color="(.*)"/U', $iitInnerHTML, $matches);
        if($is_color_matched = true){
            if(empty($matches[1])) {
                continue;
            }
            $raw_color = $matches[1];
            
            switch($raw_color) {
                case '#ae0000':
                case '#d10202':
                    $color = 'red';
                    break;
                case '#0d56ba':
                    $color = 'blue';
                    break;
                case '#5a1196':
                    $color = 'purple';
                    break;
                case '#ff4500':
                    $color = 'orange';
                    break;
                case '#478300':
                    $color = 'green';
                    break;
                case '#000000':
                    $color = 'black';
                    break;
                default:
                    break;
            }
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
            $keyword_text = $matches[4][0];
        } else {
            continue;
        }
        
        //insert_verse_keyword($verse_id, $color, $keyword, $keyword_description, $amysql)
        //$test = 1;
    }
    
    echo $message;
?>