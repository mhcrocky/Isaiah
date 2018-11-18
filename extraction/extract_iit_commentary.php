<?php
    ini_set('memory_limit', '768M');
    require('lib/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    $site_root = 'C:/Users/Tekton/Documents/isaiahexplained_code/';
    $commentary_html = $site_root . 'Code/Laravel/extraction/doc/E-Book Apocalyptic Commentary of Isaiah.html';

    $book_title = 'Isaiah IIT';

    $message = 'success';

    $chapter_number = 0;
    $chapter_id = 0;
    //$last_chapter = 0;
    $commentary_pending_html = '';
    $subject_verses = '';
    $verses = array();
    $amysql = null;

    $html = file_get_html($commentary_html);

    foreach($html->find('p[class=western]') as $commentaryHTML) {
        $commentaryInnerHTML = trim($commentaryHTML->innertext);
        if(empty($amysql)) {
            $amysql = get_amysql();
        }
        $is_header = preg_match('/^(\d{1,2}):\d{1,2}/', $commentaryInnerHTML, $matches);
        if($is_header == true) {
            if(!empty($commentary_pending_html)) {
                $subject_verses = '';
                $verses = array();
                $subject_and_verses = extractVerses($last_header_html);
                $subject_verses = htmlentities('(' . $subject_and_verses['subject_verses'] . ')');
                $verses = $subject_and_verses['verses'];
                $commentary_id = insert_commentary(htmlentities($commentary_pending_html), $subject_verses, $amysql);
                $last_header_html = '';
                $commentary_pending_html = '';
                foreach($verses as $verse) {
                    $verse_id = get_book_verse_id($book_title, $chapter_number, $verse, $amysql);
                    $chapter_id = get_book_chapter_id($book_title, $chapter_number, $amysql);
                    $index_id = insert_commentary_index($chapter_id, $verse_id, $verse, $commentary_id, $amysql);
                }
                $header_html = getHeaderHTML($chapter_number, $verses, $amysql);
                insert_commentary_header($commentary_id, $header_html, $amysql);
            }
            if(!empty($matches) && !empty($matches[1])) {
                $chapter_number = $matches[1];
                $last_header_html = $commentaryInnerHTML;
                /*if($last_chapter != 0 && $last_chapter != $chapter_number) {
                    $last_chapter = $chapter_number;
                }*/
            }
        } else {
            $commentary_pending_html .= <<<EOT
<p>${commentaryInnerHTML}</p>
EOT;
        }
    }
    if(!empty($commentary_pending_html)) {
        $subject_verses = '';
        $verses = array();
        $subject_and_verses = extractVerses($last_header_html);
        $subject_verses = htmlentities('(' . $subject_and_verses['subject_verses'] . ')');
        $verses = $subject_and_verses['verses'];
        $commentary_id = insert_commentary(htmlentities($commentary_pending_html), $subject_verses, $amysql);
        $last_header_html = '';
        $commentary_pending_html = '';
        foreach($verses as $verse) {
            $verse_id = get_book_verse_id($book_title, $chapter_number, $verse, $amysql);
            $chapter_id = get_book_chapter_id($book_title, $chapter_number, $amysql);
            $index_id = insert_commentary_index($chapter_id, $verse_id, $verse, $commentary_id, $amysql);
        }
        $header_html = getHeaderHTML($chapter_number, $verses, $amysql);
        insert_commentary_header($commentary_id, $header_html, $amysql);
    }
    echo $message;

/**
 * Get header html
 *
 * @param int $chapter_number
 * @param array $verses
 * @param AMysql $amysql
 * @return string
 */
function getHeaderHTML($chapter_number, $verses, &$amysql) {
    //Prose:
    //<span class="prose"><span class="verse-number">1</span> When Ahaz son of Jotham, the son of Uzziah, was king of Judah, Rezin king of Aram and Pekah son of Remaliah king of Israel came up to Jerusalem to wage war against it, but could not overpower it.</span>

    //Poetry:
    /*
    <span class="poetry"><span class="verse-number">7</span> Thus says my Lord Jehovah:
        <span class="indent">It shall not occur or transpire.</span>
    </span>
    <span class="poetry"><span class="verse-number">8</span> For as surely as Damascus is the capital of Aram
        <span class="indent">and Rezin the head of Damascus, within sixty-five<sup>2</sup> years shall Ephraim be shattered as a nation.</span>
    </span>
    <span class="poetry"><span class="verse-number">9</span> But as surely as Samaria is the capital of Ephraim
        <span class="indent">and the son of Remaliah the head of Samaria,</span>
        you will not believe it,
        <span class="indent">because you are not loyal.</span>
    </span>
    */
    $chapter_text = get_book_chapter('Isaiah IIT', $chapter_number, $amysql);

    $last_segment_id = 0;
    $segment_id = 1;

    $verse_count = count($chapter_text);

    $iit_html = '';
    $chapter_keywords_html = '';
    $is_prose_inline = false;

    for($i = 0; $i < $verse_count; $i++) {
        $is_matched = false;
        $verse_id = $chapter_text[$i]['verse_id'];
        $verse_number = strrtrim($chapter_text[$i]['verse_number'], '.0');

        foreach($verses as $verse) {
            if($verse_number == $verse) {
                $is_matched = true;
            }
        }

        $scripture_text = html_entity_decode($chapter_text[$i]['scripture_text']);
        $custom_html = html_entity_decode($chapter_text[$i]['one_col_html']);

        if(!empty($custom_html)) {
            list($custom_html, $chapter_keywords_html) = buildKeywordsHTML($custom_html, $verse_id, $chapter_keywords_html, $amysql);
        } else {
            list($scripture_text, $chapter_keywords_html) = buildKeywordsHTML($scripture_text, $verse_id, $chapter_keywords_html, $amysql);
        }

        $segment_id = $chapter_text[$i]['segment_id'];
        $is_poetry = $chapter_text[$i]['is_poetry'];
        $is_prose = ($is_poetry == false ? true : false);
        $indent_start = '';
        $indent_end = '';

        $j = $i + 1;
        if($j != $verse_count) {
            $next_segment_id = $chapter_text[$j]['segment_id'];
            $next_is_poetry = $chapter_text[$j]['is_poetry'];
            $next_is_prose = ($next_is_poetry == false ? true : false);
        } else {
            $next_segment_id = 0;
            $next_is_poetry = false;
            $next_is_prose = false;
        }

        $indent_start = '<span>';
        $indent_end = '</span>';
        // If is poetry and starts with a span, we need to strip that span and indent the whole span
        if($is_poetry == true) {
            $is_poem_indented = preg_match('/^<span class="indent">.*/', $scripture_text);
            if($is_poem_indented == true) {
                $scripture_text = preg_replace('/^<span class="indent">(.*)<\/span>$/', '$1', $scripture_text);
                $indent_start = '<span class="indent">';
                $indent_end = '</span>';
            }
        }

        $segment_ids = array('last_segment_id' => $last_segment_id, 'next_segment_id' => $next_segment_id, 'segment_id' => $segment_id);

        $is_prose_inline = ($is_prose == true && isProseInline($segment_ids) ? true : false);
        $space = getSpace($is_prose, $is_prose_inline, $next_is_prose, $segment_ids);

        $verse_class = getVerseClass($is_prose, $is_prose_inline);

        $is_chapter_number_first = false;
        if($i == 0) {
            if($is_chapter_number_first == true) {
                if($chapter_number < 10) {
                    $number_class = 'chapter-number-single';
                } else {
                    $number_class = 'chapter-number-double';
                }
                $display_verse_number = $chapter_number;
            } else {
                $number_class = 'verse-number';
                $display_verse_number = $verse_number;
            }
        } else {
            $number_class = 'verse-number';
            $display_verse_number = $verse_number;
        }

        if($is_matched) {
            if (!empty($custom_html)) {
                $iit_html .= <<<EOT
$custom_html
EOT;
            } else {
                $iit_html .= <<<EOT
<span class="${verse_class}">
${indent_start}<span class="${number_class}">${display_verse_number}</span> ${scripture_text}${indent_end}
</span>${space}
EOT;
            }
        }

        // Clear $is_prose_inline if necessary
        if($is_prose_inline == true) {
            if($next_segment_id != $segment_id) {
                $is_prose_inline = false;
            }
        }

        $last_segment_id = $segment_id;
    }

    if(!empty($space)) {
        $iit_html = strrtrim($iit_html, $space);
    }

    $iit_html = preg_replace('/<sup>(.*)<\/sup>/U', '<sup><a id="commentary_sup_$1" href="#commentary-footnote-$1" data-toggle="tooltip">$1</a></sup>', $iit_html);

    $iit_html .= "$chapter_keywords_html\r\n";

    return htmlentities($iit_html);
}

/**
 * Gets prose CSS class based on if verse is a prose block segment
 *
 * @param bool $is_prose is the verse prose
 * @param bool $is_prose_inline does next verse belong to the same prose segment
 * @return string inline text, if applicable
 */
function getVerseClass($is_prose, $is_prose_inline = false) {
    if($is_prose) {
        if($is_prose_inline) {
            $verse_class = 'prose inline';
        } else {
            $verse_class = 'prose';
        }
    } else {
        $verse_class = 'poetry';
    }

    return $verse_class;
}

function isProseInline($segment_ids) {
    $last_segment_id = $segment_ids['last_segment_id'];
    $segment_id = $segment_ids['segment_id'];
    $next_segment_id = $segment_ids['next_segment_id'];

    if($last_segment_id == $segment_id || $next_segment_id == $segment_id) {
        $is_prose_inline = true;
    } else {
        $is_prose_inline = false;
    }

    return $is_prose_inline;
}

/**
 * Gets spacer html where needed
 *
 * @param bool $is_prose
 * @param bool $is_prose_inline
 * @param bool $next_is_prose
 * @param array $segment_ids
 * @return html spacer html
 */
function getSpace($is_prose, $is_prose_inline, $next_is_prose, $segment_ids) {
    $segment_id = $segment_ids['segment_id'];
    $next_segment_id = $segment_ids['next_segment_id'];
    $spacer = '';
    $normal_spacer = 'spacer';
    $prose_spacer = 'prose-spacer';
    $nl = PHP_EOL;

    if($is_prose == true) {
        // Prose
        if($next_is_prose == true) {
            if($segment_id != $next_segment_id) {
                $spacer = $prose_spacer;
            }
        } else {
            $spacer = $normal_spacer;
        }
    } else {
        // Poetry
        if($next_is_prose == true) {
            $spacer = $normal_spacer;
        } else {
            if($segment_id != $next_segment_id) {
                $spacer = $normal_spacer;
            }
        }
    }

    return (!empty($spacer) ? "${nl}<div class=\"${spacer}\"></div>" : $nl);
}

/**
 * @param $commentaryInnerHTML
 * @return array
 */
function extractVerses($commentaryInnerHTML)
{
    $subject_and_verses = array();
    $is_subject_a = preg_match('/^((\d{1,2}:\d{1,2}–\d{1,2});\s(\d{1,2}:\d{1,2}\*);\s(\d{1,2}:\d{1,2}))/', $commentaryInnerHTML, $subject_a_matches);
    if ($is_subject_a) {
        // 40:18–19;\n41:7*; 40:20
        $subject_verses = $subject_a_matches[0];
        $verses[] = 18;
        $verses[] = 19;
        $verses[] = 41.7;
        $verses[] = 20;
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_b = preg_match('/^((\d{1,2}:\d{1,2}\*),(\d{1,2}–\d{1,2}))/', $commentaryInnerHTML, $subject_b_matches);
    if ($is_subject_b) {
        // 38:22*,7–8
        $subject_verses = $subject_b_matches[0];
        $verses[] = 22;
        $verses[] = 7;
        $verses[] = 8;
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_c = preg_match('/^\d{1,2}:(\d{1,2}),(\d{1,2})–(\d{1,2})/', $commentaryInnerHTML, $subject_c_matches);
    if ($is_subject_c) {
        // 37:5,3–4
        $subject_verses = $subject_c_matches[0];
        $verses[] = 5;
        $verses[] = 3;
        $verses[] = 4;
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_d = preg_match('/^\d{1,2}:(\d{1,2})\*/', $commentaryInnerHTML, $subject_d_matches);
    if ($is_subject_d) {
        // 32:19*
        $subject_verses = $subject_d_matches[0];
        $verses[] = $subject_d_matches[1];
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_e = preg_match('/^\d{1,2}:(\d{1,2}),(\d{1,2})/', $commentaryInnerHTML, $subject_e_matches);
    if ($is_subject_e) {
        // 2:19,21
        $subject_verses = $subject_e_matches[0];
        $verses[] = $subject_e_matches[1];
        $verses[] = $subject_e_matches[2];
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_f = preg_match('/^\d{1,2}:(\d{1,2})–(\d{1,2})/', $commentaryInnerHTML, $subject_f_matches);
    if ($is_subject_f) {
        // 2:7–8
        $subject_verses = $subject_f_matches[0];
        $verse_range_start = $subject_f_matches[1];
        $verse_range_end = $subject_f_matches[2];
        $verses = findVerses($verse_range_start, $verse_range_end);

        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $is_subject_g = preg_match('/^\d{1,2}:(\d{1,2})/', $commentaryInnerHTML, $subject_g_matches);
    if ($is_subject_g) {
        // 2:7
        $subject_verses = $subject_g_matches[0];
        $verses[] = $subject_g_matches[1];
        $subject_and_verses['subject_verses'] = $subject_verses;
        $subject_and_verses['verses'] = $verses;
        return $subject_and_verses;
    }
    $subject_and_verses['subject_verses'] = '';
    $subject_and_verses['verses'] = array();
    return $subject_and_verses;
}

function findVerses($verse_range_start, $verse_range_end) {
    $verses = array();

    for($i = $verse_range_start; $i <= $verse_range_end; $i++) {
        $verses[] = (int) $i;
    }

    return $verses;
}



/**
 * @param $scripture_text
 * @param $verse_id
 * @param $chapter_keywords_html
 * @param $amysql
 * @return array
 */
function buildKeywordsHTML($scripture_text, $verse_id, $chapter_keywords_html, &$amysql)
{
    $has_keyword = preg_match('/<b>.*<\/b>/U', $scripture_text);
    if ($has_keyword == true) {
        $iit_keywords = getIITKeyword($verse_id, $amysql);
        if(!empty($iit_keywords)) {
            //$keywords_found = array();
            foreach ($iit_keywords as $iit_keyword) {
                $keyword_id = $iit_keyword['keyword_id'];
                $keyword = $iit_keyword['keyword'];
                /*$keywords_found[] = $keyword;
                $keywords_count = array_count_values($keywords_found);
                $identical_keywords = $keywords_count[$keyword];*/
                $color = $iit_keyword['color_name'];
                $section = '';
                $section_tab = 'commentary';
                if ($section_tab == 'one-col') {
                    $section = 'one_col';
                } elseif ($section_tab == 'three-col') {
                    $section = 'three_col';
                } elseif ($section_tab == 'commentary') {
                    $section = 'commentary';
                } elseif ($section_tab == 'concordance') {
                    $section = 'concordance';
                }
                $pattern = '/<b>(' . $keyword . ')<\/b>/Ui';
                $replacement = '<b><a id="' . $keyword_id . '_' . $section . '_keyword_verse" name="' . $section . '" href="#defmodal" class="modal-trigger keyword-modal def-trigger ' . $color . '" data-toggle="modal">$1</a></b>';
                //$scripture_text = pregReplaceNth($pattern, $replacement, $scripture_text, $identical_keywords);
                $scripture_text = preg_replace($pattern, $replacement, $scripture_text);
                $chapter_keywords_html .= '<div id="' . $keyword_id . '_' . $section . '_keyword_description" name="' . $section . '" style="display: none;">' . html_entity_decode($iit_keyword['keyword_description']) . '</div>';
                $chapter_keywords_html .= '<div id="' . $keyword_id . '_' . $section . '_keyword_color" name="' . $section . '" style="display: none;">' . $color . '</div>';
            }
            return array($scripture_text, $chapter_keywords_html);
        } else {
            return array($scripture_text, $chapter_keywords_html);
        }
    }
    return array($scripture_text, $chapter_keywords_html);
}

/**
 * @param $pattern
 * @param $replacement
 * @param $subject
 * @param int $nth
 * @return mixed
 */
function pregReplaceNth($pattern, $replacement, $subject, $nth=1) {
    return preg_replace_callback($pattern,
        function($found) use (&$pattern, &$replacement, &$nth) {
            $nth--;
            if ($nth==0) return preg_replace($pattern, $replacement, reset($found) );
            return reset($found);
        }, $subject,$nth  );
}
?>