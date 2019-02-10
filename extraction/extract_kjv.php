<?php
ini_set('memory_limit', '512M');
require('../includes/simplehtmldom_1_5/simple_html_dom.php');
require('isaiah_db.php');

$desktop = 'C:/Users/thetekton/Desktop/';
$filename = "C:/Users/thetekton/Desktop/isaiahexplained.com/html/King_James_Version_of_Isaiah.html";
if(checkHTMLArchive($filename)) {
    $html = file_get_html($filename);
} else {
    echo "Error: ${filename} not found.";
}

$book_title = 'Isaiah KJV';

/*
<P CLASS="western" STYLE="margin-bottom: 0in"><BR>
</P>
<P CLASS="western" STYLE="margin-bottom: 0in"><B>Isaiah 1</B></P>
<P CLASS="western" STYLE="margin-bottom: 0in"><BR>
</P>
<P CLASS="western" STYLE="margin-bottom: 0in">1  The vision of Isaiah
the son of Amoz, which he saw concerning Judah and Jerusalem in the
days of Uzziah, Jotham, Ahaz, <I>and</I> Hezekiah, kings of Judah.</P>
<P CLASS="western" STYLE="margin-bottom: 0in"><BR>
</P>
<P CLASS="western" STYLE="margin-bottom: 0in"><B>Isaiah 2</B></P>
<P CLASS="western" STYLE="margin-bottom: 0in"><BR>
</P>
<P CLASS="western" STYLE="margin-bottom: 0in">1  The word that Isaiah
the son of Amoz saw concerning Judah and Jerusalem.</P>
*/

//$verses = array('chapter', 'verse', 'text');
$verses = array();
$chapter = 0;
$chapter_id = 0;
foreach($html->find('p[class=western]') as $kjvHTML):
    $verse_text = trim($kjvHTML->plaintext);
    if(empty($verse_text) || $verse_text == "King James Version of Isaiah") {
        continue;
    }
    $pattern = '/^Isaiah\s+(\d+)$/';
    preg_match($pattern, $verse_text, $matches, PREG_OFFSET_CAPTURE);
    if(!empty($matches)) {
        $chapter = (int)$matches[1][0];
        $chapter_id = get_book_chapter_id($book_title, $chapter, $amysql);
        continue;
    } else {
        if(!empty($chapter)) {
            $pattern = '/(\d+)\s+(.*)/s';
            preg_match($pattern, $verse_text, $matches, PREG_OFFSET_CAPTURE);
            if(!empty($matches) && !empty($matches[1][0]) && !empty($matches[2][0])) {
                $current_verse = (int) $matches[1][0];
                $verse_text = $matches[2][0];
                //$verses[] = array($chapter, $current_verse, $verse_text);
                
                //$success = update_chapter_verse($chapter_id, $current_verse, $verse_text, 1, false, $amysql);
                $success = insert_chapter_verse($chapter_id, $current_verse, $verse_text, 1, false, $amysql);
            }
        }
    }
endforeach;
                   
//array2csv($verses, "${desktop}isaiah_kjv.csv");
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