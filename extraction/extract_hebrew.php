<?php
ini_set('memory_limit', '512M');
require('../includes/simplehtmldom_1_5/simple_html_dom.php');
require('isaiah_db.php');

$start_chapter = 1;
$end_chapter = 66;

$desktop = 'C:/Users/thetekton/Desktop/';

$book_title = 'Isaiah Hebrew';

$verses = array();
for($chapter = $start_chapter; $chapter <= $end_chapter; $chapter++):
    if($chapter < 10) {
        $pad_chapter = sprintf("%02s", $chapter);
    } else {
        $pad_chapter = $chapter;
    }
    $filename = "isaiah_ch_${pad_chapter}.html";
    //$localPath = "C:/Users/thetekton/Desktop/isaiahexplained.com/html/isaiah_ch_1.html";
    $localPath = "C:/Users/thetekton/Desktop/isaiahexplained.com/html/scrape/${filename}";
    if(checkHTMLArchive($localPath)) {
        $html = file_get_html($localPath);
    } else {
        //$html = file_get_html('http://isaiahexplained.com/isaiah_ch_1.html');
        $html = file_get_html("http://isaiahexplained.com/${filename}");
        createHTMLArchive($html, $localPath);
        sleep(1);
    }
    
    $chapter_id = get_book_chapter_id($book_title, $chapter, $amysql);
    
    $current_verse = 1;
    foreach($html->find('td[class=heb]') as $hebrewHTML):
        $verse_text = htmlentities(trim($hebrewHTML->plaintext));
        //$verses[] = array($chapter, $current_verse, $verse_text);
        $success = update_chapter_verse($chapter_id, $current_verse, $verse_text, 1, false, $amysql);
        //$success = insert_chapter_verse($chapter_id, $current_verse, $verse_text, 1, false, $amysql);
        $current_verse++;
    endforeach;
endfor;


//array2csv($verses, "${desktop}isaiah_hebrew_ch_${start_chapter}-${end_chapter}.csv");

echo "Complete";

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