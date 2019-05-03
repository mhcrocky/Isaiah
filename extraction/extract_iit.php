<?php
ini_set('memory_limit', '512M');
require('../includes/simplehtmldom_1_5/simple_html_dom.php');

$desktop = 'C:/Users/thetekton/Desktop/';
$filename = "${desktop}isaiahexplained.com/html/Isaiah_Prophecy_Translation.html";
if(checkHTMLArchive($filename)) {
    $html = file_get_html($filename);
} else {
    echo "Error: ${filename} not found.";
    exit;
}
                                              
$verses = array();
$poem = array();
$chapter = 0;
foreach($html->find('div[id^=Section]') as $divHTML):
    $chapter++;
    $verse_number = null;
    // Each block of prose/poetry needs a unique segment ID. This will increment at the end of each block.
    $segment_id = 1;
    $is_last_line_connected = false;
    foreach($divHTML->find('p[class=western]') as $iitHTML):
        $verse_text = '';
        $iitInnerHTML = $iitHTML->innertext;
        $space_count = 0;
        if(!empty($iitInnerHTML)) {
            preg_match('/<sup>\w<\/sup>/', $iitInnerHTML, $matches, PREG_OFFSET_CAPTURE);
            if(!empty($matches)) {
                $verse_text = strip_tags($iitInnerHTML, '<strong><em><b><i><sup>');
                $verse_text = preg_replace('/\t+/', '', $verse_text);
                $verse_text = preg_replace('/(.*)<sup>(<i><sup>\w<\/sup><\/i>)<\/sup>(.*)/s', '\1\2\3', $verse_text);
                $space_count = strspn($verse_text, ' ');
                $verse_text = trim($verse_text);
            } else {
                $verse_text = strip_tags($iitInnerHTML, '<strong><em><b><i><sup>');
                $verse_text = preg_replace('/\t+/', '', $verse_text);
                $space_count = strspn($verse_text, ' ');
                $verse_text = trim($verse_text);
            }
        } else {
            $test = 1;
        }
        if(empty($verse_text) || $verse_text == "1The Book of Isaiah") {
            continue;
        } else {
            $pattern = '/(\d*)\s*(\D+)/s';
            $is_matched = preg_match_all($pattern, $verse_text, $matches, PREG_OFFSET_CAPTURE);
            $is_poem_segment = false;
            if(!empty($is_matched)) {
                if($is_matched > 1) {
                    // We know this is prose, because, multiple verses span one line
                    $is_poem_segment = false;
                } else {
                    // segment number needs to be reset to 1, on the next iteration, only if the next line is a <br>
                    $segment_check_result = poemSegmentCheck($html, $iitInnerHTML, $is_last_line_connected);
                    $is_poem_segment = $segment_check_result['is_poem_segment'];
                    $is_next_a_br = $segment_check_result['is_br'];
                }
                $verse_numbers = $matches[1];
                $verse_text_matches = $matches[2];
                for($i = 0; $i < $is_matched; $i++) {
                    $test_verse_number = $verse_numbers[$i][0];
                    if(!empty($test_verse_number)){
                        // Could be first verse of a poem, or prose.
                        $verse_number = (int) $test_verse_number;
                        $verse_text = trim($verse_text_matches[$i][0]);
                        $verse_text = htmlentities($verse_text);
                    } else {
                        // Part of a poem? If so, we need to convert indentation into 5/10 spaced indent. This is a fuzzy number, as not all indents are uniform. 
                        if($is_poem_segment == true) {
                            $verse_text = trim($verse_text_matches[$i][0]);
                            if(!empty($space_count) && $space_count >= 7) {
                                $verse_text = '<span class="indent">' . $verse_text . '</span>';
                            } else {
                                $verse_text = $verse_text;
                            }
                            $verse_text = htmlentities($verse_text);
                        } else {
                            $verse_text = trim($verse_text_matches[$i][0]);
                            $verse_text = htmlentities($verse_text);
                            //$segment_id++; 
                        }
                    }
                    // Stopped here. Need to figure out how to deal with segments, as poems can span many verses. I think, as long as we know we're in poem-mode,
                    // we should be able to differentiate segments based on line breaks
                    if($is_poem_segment == true) {
                        if($is_next_a_br == false) {
                            $is_last_line_connected = true;
                            //$poem[] = array('verse_text' => $verse_text, 'segment_id' => $segment_id);
                            $poem[]['verse_text'] = $verse_text;
                            //$segment_id++;
                        } else {
                            //$segment_id = 1;
                            $is_last_line_connected = false;
                            // Here we will rebuild our poem so it can be in one row 
                            $verse_text = '';
                            foreach($poem as $poem_line) {
                                // We need to count spacing and use 5-spaced if less than 7 spaces and use 10-spaced if greater than or equal to 7
                                $verse_text = $poem_line;
                            }
                            $verses[] = array($chapter, $verse_number, $verse_text, $segment_id, (int) $is_poem_segment);
                            //$segment_id++;
                            $poem = array();
                        }
                    } else {
                        $verses[] = array($chapter, $verse_number, $verse_text, $segment_id, (int) $is_poem_segment);
                        $is_last_line_connected = false;
                    }
                    if($is_poem_segment == false || $is_next_a_br == true) {
                        $segment_id++;
                    } else {
                        $test = 1;
                    }
                }
            } else {
                continue;
            }
        }
    endforeach;
endforeach;
                   
array2csv($verses, "${desktop}isaiah_iit.csv");
echo "Complete";

function poemSegmentCheck($html, $iitInnerHTML, $is_last_line_connected) {
    $segment_check_result = array('is_poem_segment' => false, 'is_br' => false);
    // This may be prose, or poetry. The only way we could know is if we traverse dom again and look-ahead to the following iteration in our find
    $is_html_found = false;
    $is_type_found = false;
    // TODO: Not sure yet if we'll need a second simple_html_dom instance
    foreach($html->find('div[id^=Section]') as $divHTML2):
        foreach($divHTML2->find('p[class=western]') as $iitHTML2):
            $iitInnerHTML2 = $iitHTML2->innertext;
            if($is_html_found == false) {
                if($iitInnerHTML2 == $iitInnerHTML) {
                    // Now on the next iteration, we can finally check if this is a poem or not
                    $is_html_found = true;
                    continue;
                } else {
                    continue;
                }
            } else {
                $trimmed_text = strip_tags($iitInnerHTML2, '<br>');
                $trimmed_text = preg_replace('/\t+/', '', $trimmed_text);
                $trimmed_text = trim($trimmed_text);
                if($trimmed_text == '<br>') {
                    $segment_check_result['is_br'] = true;
                    if($is_last_line_connected == true) {
                        $segment_check_result['is_poem_segment'] = true;
                    } else {
                        $segment_check_result['is_poem_segment'] = false;
                    }
                } else {
                    $segment_check_result['is_br'] = false;
                    // Check for verse number? Indentation spacing is not uniform and may introduce errors without special counting or something
                    $pattern2 = '/(\d*)\s*\D+/s';
                    $is_matched_2 = preg_match($pattern2, $trimmed_text, $matches2, PREG_OFFSET_CAPTURE);
                    if(!empty($matches2[1][0])) {
                        $segment_check_result['is_poem_segment'] = false;
                    } else {
                        // The line is a poem segment. Until we hit a break, we know we have to continue constructing our verse row
                        $segment_check_result['is_poem_segment'] = true;
                    }
                }
                $is_type_found = true;
                break;
            }
        endforeach;
        
        if($is_type_found == true) {
            break;
        } else {
            continue;
        }
    endforeach;
    
    return $segment_check_result;
}

function checkHTMLArchive($filename) {
    $isArchived = false;
    if(file_exists($filename)) {
        $filesize = filesize($filename);
        if(!empty($filesize)) {
            $isArchived = true;
        } else {
            $isArchived = false;
        }
    } else {
        $isArchived = false;
    }
    return $isArchived;
}

function createHTMLArchive($html, $filename) {
    $fp = fopen($filename, 'w');
    fwrite($fp, $html);
    fclose($fp);
}

function array2csv(array &$array, $filename) {
   if (count($array) == 0) {
     return null;
   }
   //ob_start();
   //$fh = fopen("php://output", 'w');
   $fh = fopen($filename, 'w');
   fputcsv($fh, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($fh, $row);
   }
   fclose($fh);
   //return ob_get_clean();
}

function write_utf8_encoded_string($string, $filename) {
    //$fh=fopen($filename,"w");
    $fh = fopen($filename,"w");
    fwrite($fh, utf8_encode($string)); 
    fclose($fh);
}
?>