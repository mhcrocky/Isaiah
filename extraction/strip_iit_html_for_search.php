<?php
    ini_set('memory_limit', '768M');
    require('isaiah_db.php');

    $book_title = 'Isaiah IIT';

    $message = 'success';

    $amysql = get_amysql();

    for($chapter_number = 1; $chapter_number <= 66; $chapter_number++) {
        if(empty($amysql)) {
            $amysql = get_amysql();
        }

        $chapter_verses = get_book_chapter($book_title, $chapter_number, $amysql);

        foreach($chapter_verses as $chapter_verse) {
            $verse_id = $chapter_verse['verse_id'];
            $scripture_text_plain = rip_tags(html_entity_decode($chapter_verse['scripture_text']));
            update_chapter_verse_with_plain_scripture($verse_id, $scripture_text_plain, $amysql);
        }

    }

    echo $message;

    function rip_tags($string) {
        $string = preg_replace('/<sup>\w<\/sup>/', '', $string);

        // ----- remove HTML TAGs -----
        $string = preg_replace ('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;
    }
?>