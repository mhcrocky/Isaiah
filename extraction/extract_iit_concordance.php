<?php
    ini_set('memory_limit', '768M');
    require('lib/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    $site_root = 'C:/Users/Tekton/Documents/isaiahexplained_code/';
    $doc_path = 'Code/Laravel/extraction/doc/concordance_html-sept-30-2014/';

    for($i = 0; $i <= 25; $i++) {
        $letter = strtoupper(toAlpha($i));
        if($letter != 'X') {
            $concordance_html = $site_root . $doc_path . "concordance${letter}.html";
            $html = file_get_html($concordance_html);
            scrapeConcordance($html);
        }
    }

    /**
     * @param simple_html_dom $html
     */
    function scrapeConcordance($html) {
        $segment_id = 1;
        $sub_segment_id = 1;
        $concordance_id = 0;
        $letter = '';
        $word = '';

        foreach ($html->find('p') as $concordanceHTML) {
            $class = $concordanceHTML->attr['class'];
            if (empty($amysql)) {
                $amysql = get_amysql();
            }
            if ($class == 'keyword') {
                $word = preg_replace('/^<a\b[^>]*>(.*)/', '$1', $concordanceHTML->innertext);
                $letter = strtoupper($word[0]);
                $concordance_id = insert_concordance($word, $letter, $amysql);
                $segment_id = 1;
                $sub_segment_id = 1;
                if (strtoupper($word) == strtoupper('PRECEPT')) {
                    $test = 1;
                }
            } else {
                $citation = preg_replace('/^<a\b[^>]*>(.*)<\/a> <span\b[^>]*>/', '$1 ', $concordanceHTML->innertext);
                $is_match = preg_match('/^(\d{1,2}):(\d{1,2})/', $citation, $matches);
                if ($is_match) {
                    if (!empty($chapter_number) && !empty($verse_number)) {
                        if ($chapter_number == $matches[1] && $verse_number == $matches[2]) {
                            $segment_id++;
                            $sub_segment_id++;
                        } else {
                            $segment_id = 1;
                            $sub_segment_id = 1;
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
                    $cited_word_count = substr_count(strtolower($citation), strtolower($word));
                    $is_displayed = true;
                    if ($cited_word_count == 1) {
                        performInserts($concordance_id, $chapter_id, $verse_id, $chapter_number, $url,
                            $subject_verse, $letter, $citation, $segment_id, $sub_segment_id, $is_displayed, $amysql);
                    } else {
                        for ($i = 0; $i < $cited_word_count; $i++) {
                            performInserts($concordance_id, $chapter_id, $verse_id, $chapter_number, $url,
                                $subject_verse, $letter, $citation, $segment_id, $sub_segment_id, $is_displayed, $amysql);
                            $is_displayed = false;
                            if ($cited_word_count != $i + 1) {
                                $sub_segment_id++;
                            }
                        }
                    }
                } else {
                    $test = 1;
                }
                $test = 1;
            }
        }
    }

    if (empty($amysql)) {
        $amysql = get_amysql();
    }
    //2807	1610	42985	28	c28v13a-PRECEPT	28:13	P	upon precept, precept upon precept	PRECEPT	1	3
    performInserts(2805, 1610, 42982, 28, 'c28v10a-PRECEPT',
        '28:10', 'P', 'upon precept, precept upon precept', 1, 4, 0, $amysql);
    performInserts(2805, 1610, 42985, 28, 'c28v13a-PRECEPT',
        '28:13', 'P', 'upon precept, precept upon precept', 1, 4, 0, $amysql);

    /**
     * @param int $concordance_id
     * @param int $chapter_id
     * @param int $verse_id
     * @param int $chapter_number
     * @param string $url
     * @param string $subject_verse
     * @param string $letter
     * @param string $citation
     * @param int $segment_id
     * @param int $sub_segment_id
     * @param bool $is_displayed
     * @param AMysql &$amysql
     */
    function performInserts($concordance_id, $chapter_id, $verse_id, $chapter_number, $url, $subject_verse, $letter,
                            $citation, $segment_id, $sub_segment_id, $is_displayed, &$amysql) {
        $citation_id = insert_concordance_citation(
            $concordance_id, $chapter_id, $verse_id, $chapter_number, $url,
            $subject_verse, $letter, $citation, $segment_id, $sub_segment_id, $amysql);
        insert_concordance_index($concordance_id, $citation_id, $is_displayed, $amysql);
    }

    function toAlpha($data, $offset = 0) {
        if(!empty($offset)) {
            $data--;
        }
        $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $alpha_flip = array_flip($alphabet);
        if($data <= 25){
            return $alphabet[$data];
        } elseif($data > 25){
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