<?php

class ChapterRepository {
    public $verse_count = 0;

    private $_iit_chapter_text = '';

    /**
     * Gets text for the chapter heading
     *
     * @param int Chapter number
     * @return string Chapter heading text
     */
    public function GetChapterHeading($chapter_number) {
        $heading = Heading::where('id', '=', $chapter_number)->first();
        return $heading->heading_text;
    }

    /**
     * Get IIT chapter html
     *
     * @param $chapter_number
     * @return string
     */
    public function GetIITChapter($chapter_number) {
        $this->_iit_chapter_text = $this->_getBookChapter('Isaiah IIT', $chapter_number);
        $chapter_text = $this->_iit_chapter_text;
        $chapter_keywords_html = '';
        $chapter_verse_order_html = '';

        $last_segment_id = 0;
        $segment_id = 1;

        $verse_count = count($chapter_text);
        $this->verse_count = $verse_count;

        $iit_html = '';
        $is_prose_inline = false;

        for($i = 0; $i < $verse_count; $i++) {
            $verse_id = $chapter_text[$i]->verse_id;
            $verse_number = $this->_strrtrim($chapter_text[$i]->verse_number, '.0');
            $scripture_text = html_entity_decode($chapter_text[$i]->scripture_text);
            $custom_html = html_entity_decode($chapter_text[$i]->one_col_html);
            $segment_id = $chapter_text[$i]->segment_id;
            $is_poetry = $chapter_text[$i]->is_poetry;
            $is_prose = ($is_poetry == false ? true : false);
            $indent_start = '';
            $indent_end = '';

            if($i + 1 != $verse_count) {
                $chapter_verse_order_html .= "$verse_number,";
            } else {
                $chapter_verse_order_html .= $verse_number;
            }

            $j = $i + 1;
            if($j != $verse_count) {
                $next_segment_id = $chapter_text[$j]->segment_id;
                $next_is_poetry = $chapter_text[$j]->is_poetry;
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

            list($scripture_text, $chapter_keywords_html) = $this->_buildKeywordsHTML($scripture_text, $verse_id, $chapter_keywords_html, 'one-col');

            $segment_ids = array('last_segment_id' => $last_segment_id, 'next_segment_id' => $next_segment_id, 'segment_id' => $segment_id);

            $is_prose_inline = ($is_prose == true && $this->_isProseInline($segment_ids) ? true : false);
            $space = $this->_getSpace($is_prose, $is_prose_inline, $next_is_prose, $segment_ids);

            $verse_class = $this->_getVerseClass($is_prose, $is_prose_inline);

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

            if(!empty($custom_html)) {
                $iit_html .= <<<EOT
\t$custom_html\r\n
EOT;
            } else {
                $iit_html .= <<<EOT
\t\t<span class="${verse_class}">\r\n
\t\t\t${indent_start}<a href="#versemodal" class="modal-trigger ${number_class}" data-toggle="modal">${display_verse_number}</a> ${scripture_text}${indent_end}
\t\t</span>${space}\r\n
EOT;
            }

            // Clear $is_prose_inline if necessary
            if($is_prose_inline == true) {
                if($next_segment_id != $segment_id) {
                    $is_prose_inline = false;
                }
            }

            $last_segment_id = $segment_id;
        }

        $iit_html = preg_replace('/<sup>(.*)<\/sup>/U', '<sup><a id="one_col_sup_$1" href="#one-col-footnote-$1" data-toggle="tooltip">$1</a></sup>', $iit_html);
        $iit_html .= $chapter_keywords_html;
        $iit_html .= '<div id="chapter-verse-order" style="display: none;">' . $chapter_verse_order_html . '</div>';

        return $iit_html;
    }

    public function GetIITFootnotesList($chapter_number) {
        return $this->getIITFootnotes($chapter_number);
    }

    /**
     * Get three column view HTML
     *
     * @param int Chapter number
     * @return string Three column view HTML
     */
    public function GetThreeColHtml($chapter_number) {
        $is_chapter_number_first = false;
        $chapter_keywords_html = '';

        $kjv_chapter_text = $this->_getBookChapter('Isaiah KJV', $chapter_number);
        if(!empty($this->_iit_chapter_text)) {
            $iit_chapter_text = $this->_iit_chapter_text;
        } else {
            $iit_chapter_text = $this->_getBookChapter('Isaiah IIT', $chapter_number);
        }
        $heb_chapter_text = $this->_getBookChapter('Isaiah Hebrew', $chapter_number);

        $last_segment_id = 0;
        $segment_id = 1;

        $three_col_html = '';

        $is_prose_inline = false;

        // 'verse_number', 'scripture_text', 'custom_html',
        $kjv = array();
        $iit = array();
        $heb = array();

        $iit_verse_count = count($iit_chapter_text);

        // IIT loop
        for($i = 0, $j = 1; $i < $iit_verse_count; $i++, $j++) {

            //$verse_id = $iit_chapter_text[$i]['verse_id'];
            $kjv_html = '';
            $iit_html = '';
            $heb_html = '';

            $iit_verse_number = $this->_strrtrim($iit_chapter_text[$i]->verse_number, '.0');
            if(!empty($kjv_chapter_text[$i])) {
                $kjv_verse_number = $this->_strrtrim($kjv_chapter_text[$i]->verse_number, '.0');
            } else {
                $kjv_verse_number = '';
            }
            /*if($iit_verse_number != $kjv_verse_number) {
                $kjv_verse_number = $kjv_verse_number . "|${iit_verse_number}";
            }*/

            if(!empty($kjv_chapter_text[$i])) {
                $kjv_scripture_text = html_entity_decode($kjv_chapter_text[$i]->scripture_text);
            } else {
                $kjv_scripture_text = '';
            }

            if(!empty($iit_chapter_text[$i]->three_col_html)) {
                $iit_scripture_text = html_entity_decode($iit_chapter_text[$i]->three_col_html);
            } else {
                $iit_scripture_text = html_entity_decode($iit_chapter_text[$i]->scripture_text);
            }

            list($iit_scripture_text, $chapter_keywords_html) = $this->_buildKeywordsHTML($iit_scripture_text, $iit_chapter_text[$i]->verse_id, $chapter_keywords_html, 'three-col');

            if(!empty($heb_chapter_text[$i])) {
                $heb_scripture_text = html_entity_decode($heb_chapter_text[$i]->scripture_text);
            } else {
                $heb_scripture_text = '';
            }

            $segment_id = $iit_chapter_text[$i]->segment_id;

            $is_iit_poetry = $iit_chapter_text[$i]->is_poetry;
            $is_iit_prose = ($is_iit_poetry == false ? true : false);

            if($j != $iit_verse_count) {
                $next_segment_id = $iit_chapter_text[$j]->segment_id;
                $next_is_poetry = $iit_chapter_text[$j]->is_poetry;
                $next_is_prose = ($next_is_poetry == false ? true : false);
            } else {
                $next_segment_id = 0;
                $next_is_poetry = false;
                $next_is_prose = false;
            }

            $indent_start = '<span>';
            $indent_end = '</span>';
            // If is poetry and starts with a span, we need to strip that span and indent the whole span
            if($is_iit_poetry == true) {
                $is_poem_indented = preg_match('/^<span class="indent">.*/', $iit_scripture_text);
                if($is_poem_indented == true) {
                    $iit_scripture_text = preg_replace('/^<span class="indent">(.*)<\/span>$/', '$1', $iit_scripture_text);
                    $indent_start = '<span class="indent">';
                    $indent_end = '</span>';
                }
            }

            $segment_ids = array('last_segment_id' => $last_segment_id, 'next_segment_id' => $next_segment_id, 'segment_id' => $segment_id);

            $is_prose_inline = ($is_iit_prose == true && $this->_isProseInline($segment_ids) ? true : false);
            $space = $this->_getSpace($is_iit_prose, $is_prose_inline, $next_is_prose, $segment_ids);

            $verse_class = $this->_getVerseClass($is_iit_prose, $is_prose_inline);

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
                    $display_verse_number = $iit_verse_number;
                }
            } else {
                $number_class = 'verse-number';
                $display_verse_number = $iit_verse_number;
            }

            /*if(!empty($iit_custom_html)) {
                $iit_html .= $iit_custom_html;
            } else {*/
            $iit_html .= <<<EOT
${indent_start}${iit_scripture_text}${indent_end}
EOT;
            /*}*/

            $three_col_html .= <<<EOT
\t\t\t<tr>
\t\t\t\t<td id="kjv_${kjv_verse_number}">${kjv_scripture_text}</td>
\t\t\t\t<td class="comp-vs-num">${kjv_verse_number}</td>
\t\t\t\t<td id="iit_${iit_verse_number}">${iit_html}</td>
\t\t\t\t<td id="heb_${kjv_verse_number}" class="heb-col">${heb_scripture_text}</td>
\t\t\t</tr>\r\n
EOT;

            // Clear $is_prose_inline if necessary
            if($is_prose_inline == true) {
                if($next_segment_id != $segment_id) {
                    $is_prose_inline = false;
                }
            }

            $last_segment_id = $segment_id;
        }

        $three_col_html = preg_replace('/<sup>(.*)<\/sup>/U', '<sup><a id="three_col_sup_$1" href="#three-col-footnote-$1" data-toggle="tooltip">$1</a></sup>', $three_col_html);

        $three_col_html .= "$chapter_keywords_html\r\n";

        return $three_col_html;
    }

    /**
     * Get Commentary view HTML
     *
     * @param int Chapter number
     * @return string Commentary view HTML
     */
    public function GetIITCommentary($chapter_number) {
        $iit_commentary_html = '';
        $headers = $this->_getCommentaryHeaders($chapter_number);
        foreach($headers as $header) {
            $iit_commentary_html .= '<div class="commentary">' . html_entity_decode($header->header) . '</div>';
            $verses = $this->_getCommentaryVerses($header->commentary_id);
            $wrapper_class = '';
            $verse_count = count($verses);
            for($i = 0; $i < $verse_count; $i++) {
                if($i + 1 != $verse_count) {
                    $wrapper_class .= 'commentary_' . $this->_strrtrim($verses[$i]->verse_number, '.0') . ' ';
                } else {
                    $wrapper_class .= 'commentary_' . $this->_strrtrim($verses[$i]->verse_number, '.0');
                }
            }
            $subject_verses = '<div class="subject_verses" style="display: none;">' . $this->_getCommentarySubjectVerses($header->commentary_id) . '</div>';
            $iit_commentary_html .= "<div class=\"$wrapper_class\">$subject_verses" . html_entity_decode($this->_getCommentary($header->commentary_id)) . '</div>';

        }
        return $iit_commentary_html;
    }

    /**
     * Get IIT Concordance html
     *
     * @param $chapter_number
     * @return string
     */
    public function GetIITConcordance($chapter_number) {
        $this->_iit_chapter_text = $this->_getBookChapter('Isaiah IIT', $chapter_number);
        $chapter_text = $this->_iit_chapter_text;
        $chapter_keywords_html = '';

        $last_segment_id = 0;
        $segment_id = 1;

        $verse_count = count($chapter_text);
        $this->verse_count = $verse_count;

        $iit_html = '';
        $is_prose_inline = false;

        for($i = 0; $i < $verse_count; $i++) {
            $verse_id = $chapter_text[$i]->verse_id;
            $verse_number = $this->_strrtrim($chapter_text[$i]->verse_number, '.0');
            $scripture_text = html_entity_decode($chapter_text[$i]->scripture_text);
            $custom_html = html_entity_decode($chapter_text[$i]->one_col_html);
            $segment_id = $chapter_text[$i]->segment_id;
            $is_poetry = $chapter_text[$i]->is_poetry;
            $is_prose = ($is_poetry == false ? true : false);
            $indent_start = '';
            $indent_end = '';

            $j = $i + 1;
            if($j != $verse_count) {
                $next_segment_id = $chapter_text[$j]->segment_id;
                $next_is_poetry = $chapter_text[$j]->is_poetry;
                $next_is_prose = ($next_is_poetry == false ? true : false);
            } else {
                $next_segment_id = 0;
                $next_is_poetry = false;
                $next_is_prose = false;
            }

            // If is poetry and starts with a span, we need to strip that span and indent the whole span
            $indent_start = '<span>';
            $indent_end = '</span>';
            if($is_poetry == true) {
                $is_poem_indented = preg_match('/^<span class="indent">.*/', $scripture_text);
                if($is_poem_indented == true) {
                    $scripture_text = preg_replace('/^<span class="indent">(.*)<\/span>$/', '$1', $scripture_text);
                    $indent_start = '<span class="indent">';
                    $indent_end = '</span>';
                }
            }

            list($scripture_text, $chapter_keywords_html) = $this->_buildKeywordsHTML($scripture_text, $verse_id, $chapter_keywords_html, 'concordance');

            $segment_ids = array('last_segment_id' => $last_segment_id, 'next_segment_id' => $next_segment_id, 'segment_id' => $segment_id);

            $is_prose_inline = ($is_prose == true && $this->_isProseInline($segment_ids) ? true : false);
            $space = $this->_getSpace($is_prose, $is_prose_inline, $next_is_prose, $segment_ids);

            $verse_class = $this->_getVerseClass($is_prose, $is_prose_inline);

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

            //url and segment_id
            if(!empty($custom_html)) {
                $custom_html = $this->_getConcordanceURL($custom_html, $chapter_number, $verse_number, $verse_id);
            } else {
                $scripture_text = $this->_getConcordanceURL($scripture_text, $chapter_number, $verse_number, $verse_id);
            }

            if(!empty($custom_html)) {
                $iit_html .= <<<EOT
<div id="iit_${display_verse_number}">
\t$custom_html\r\n
</div>\r\n
$chapter_keywords_html\r\n
EOT;
            } else {
                $iit_html .= <<<EOT
\t<div id="iit_${display_verse_number}">
\t\t<span class="${verse_class}">
\t\t\t${indent_start}<a href="#versemodal" class="modal-trigger ${number_class}" data-toggle="modal">${display_verse_number}</a> ${scripture_text}${indent_end}
\t\t</span>${space}
\t</div>\r\n
$chapter_keywords_html\r\n
EOT;
            }

            /*if($chapter_number == 28 && $verse_number == 13) {
                echo $iit_html; exit;
            }*/

            // Clear $is_prose_inline if necessary
            if($is_prose_inline == true) {
                if($next_segment_id != $segment_id) {
                    $is_prose_inline = false;
                }
            }

            $last_segment_id = $segment_id;
        }

        $iit_html = preg_replace('/<sup>(.*)<\/sup>/U', '<sup><a id="one_col_sup_$1" href="#one-col-footnote-$1" data-toggle="tooltip">$1</a></sup>', $iit_html);

        return $iit_html;
    }

    /**
     * Get the Concordance urls for the words cited in scripture
     *
     * @param string $scripture_text
     * @param int $chapter_number
     * @param int $verse_number
     * @param int $verse_id
     * @return string
     */
    private function _getConcordanceURL($scripture_text, $chapter_number, $verse_number, $verse_id) {
        $concordance_verse = $this->_getConcordanceVerse($verse_id);

        foreach($concordance_verse as $citation) {
            $word = $citation->word;
            $url = preg_replace("/($word)(?!=)/", 'zzz$1zzz', $citation->url);
            $fixed_word = preg_replace('/(.*)(?!=)/', 'zzz$1zzz', $word);
            $segment_id = $citation->segment_id;
            $sub_segment_id = $citation->sub_segment_id;
            $letter = $word[0];
            $pattern = "/\b(${word})(?!=)\b/i";
            $replacement = '<a href="/concordance/' . $letter . '?citation=' . $url . '#' . $fixed_word . '">$1</a>';
            /*if($chapter_number == 28) {
                if($verse_number == 10 || $verse_number == 13) {
                    $test = 1;
                }
            }*/
            $scripture_text = $this->_pregReplaceNth($pattern, $replacement, $scripture_text, $sub_segment_id);
        }

        return preg_replace('/zzz/', '', $scripture_text);
    }

    /**
     * Replace only the Nth occurrence of $pattern
     * http://php.net/manual/en/function.preg-replace.php#112400
     *
     * @param $pattern
     * @param $replacement
     * @param $subject
     * @param int $nth
     * @return mixed
     */
    private function _pregReplaceNth($pattern, $replacement, $subject, $nth=1) {
        return preg_replace_callback($pattern,
            function($found) use (&$pattern, &$replacement, &$nth) {
                $nth--;
                if ($nth==0) return preg_replace($pattern, $replacement, reset($found) );
                return reset($found);
            }, $subject,$nth  );
    }

    /**
     * Gets prose CSS class based on if verse is a prose block segment
     *
     * @param bool $is_prose is the verse prose
     * @param bool $is_prose_inline does next verse belong to the same prose segment
     * @return string inline text, if applicable
     */
    private function _getVerseClass($is_prose, $is_prose_inline = false) {
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

    /**
     * Tells if a space is needed after this verse's span
     *
     * @param bool $is_prose
     * @param bool $is_prose_inline
     * @param bool $next_is_prose
     * @param array $segment_ids
     *
     * @return bool Is verse's span ending spaced
     */
    private function _isSpaced($is_prose, $is_prose_inline, $next_is_prose, $segment_ids) {
        $segment_id = $segment_ids['segment_id'];
        $next_segment_id = $segment_ids['next_segment_id'];

        if($is_prose == true) {
            // Prose
            if($next_is_prose == true) {
                $is_spaced = false;
            } else {
                $is_spaced = true;
            }
        } else {
            // Poetry
            if($next_is_prose == true) {
                $is_spaced = true;
            } else {
                if($segment_id == $next_segment_id) {
                    $is_spaced = false;
                } else {
                    $is_spaced = true;
                }
            }
        }

        return $is_spaced;
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
    private function _getSpace($is_prose, $is_prose_inline, $next_is_prose, $segment_ids) {
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
     * Tells if prose is part of an inline block
     *
     * @param array $segment_ids list of last, current, and next segment ids
     * @return bool Is prose inline
     */
    private function _isProseInline($segment_ids) {
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
     * Strip a string from the end of a string
     *
     * @param mixed $message the input string
     * @param mixed $strip string to remove
     *
     * @return string the modified string
     */
    private function _strrtrim($message, $strip) {
        $lines = explode($strip, $message);
        $last  = '';
        do {
            $last = array_pop($lines);
        } while (empty($last) && (count($lines)));
        return implode($strip, array_merge($lines, array($last)));
    }

    /**
     * Get the book chapter's html
     *
     * @param $book_title
     * @param $chapter_number
     * @return string
     */
    private function _getBookChapter($book_title, $chapter_number) {
        $sql = 'SELECT
          `verses`.`id` AS `verse_id`,
          `verses`.`verse_number` AS `verse_number`,
          `verses`.`scripture_text` AS `scripture_text`,
          `verses`.`segment_id` AS `segment_id`,
          `verses`.`is_poetry` AS `is_poetry`,
          `verses`.`one_col_html` AS `one_col_html`,
          `verses`.`three_col_html` AS `three_col_html`
        FROM (((`volumes`
          JOIN `books`
            ON ((`books`.`volume_id` = `volumes`.`id`)))
          JOIN `chapters`
            ON ((`chapters`.`book_id` = `books`.`id`)))
          JOIN `verses`
            ON ((`verses`.`chapter_id` = `chapters`.`id`)))
        WHERE (`books`.`book_title` = ?
          AND `chapters`.`chapter_number` = ?)
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `verses`.`segment_id`, `verses`.`id`';

        $results = DB::select($sql, array($book_title, $chapter_number));

        return $results;
    }

    private function getIITFootnotes($chapter_number) {
        // SELECT verse_number, letter, note FROM iit_footnotes WHERE chapter_id = ?
        $sql = 'SELECT
          `iit_footnotes`.`id` AS `footnote_id`,
          `iit_footnotes`.`verse_number` DIV 1 AS `verse_number`,
          `iit_footnotes`.`letter` AS `letter`,
          `iit_footnotes`.`note` AS `footnote_text`
        FROM (((`volumes`
          JOIN `books`
            ON ((`books`.`volume_id` = `volumes`.`id`)))
          JOIN `chapters`
            ON ((`chapters`.`book_id` = `books`.`id`)))
          JOIN `iit_footnotes`
            ON ((`iit_footnotes`.`chapter_id` = `chapters`.`id`)))
        WHERE (`books`.`book_title` = \'Isaiah IIT\'
          AND `chapters`.`chapter_number` = ?)
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `iit_footnotes`.`id`';

        $results = DB::select($sql, array($chapter_number));

        $result_count = count($results);
        for($i = 0; $i < $result_count; $i++) {
            $results[$i]->footnote_text = html_entity_decode($results[$i]->footnote_text);
        }

        return $results;
    }

    private function getIITKeyword($verse_id) {
        $sql = 'SELECT id as keyword_id, color_name, keyword, keyword_description
                FROM iit_keywords
                WHERE verse_id = ?';

        $results = DB::select($sql, array($verse_id));

        return $results;
    }

    /**
     * Get the book chapter's commentary headers
     *
     * @param $chapter_number
     * @return array
     */
    private function _getCommentaryHeaders($chapter_number) {
        $sql = 'SELECT
          `iit_commentary_index`.`id` AS index_id,
          `iit_commentary_index`.`chapter_id` AS chapter_id,
          `iit_commentary_index`.`verse_id` AS verse_id,
          `iit_commentary_index`.`commentary_id` AS commentary_id,
          `iit_commentary_headers`.`header` AS header
        FROM (((`volumes`
          JOIN `books`
            ON ((`books`.`volume_id` = `volumes`.`id`)))
          JOIN `chapters`
            ON ((`chapters`.`book_id` = `books`.`id`)))
          JOIN `iit_commentary_index`
            ON ((`iit_commentary_index`.`chapter_id` = `chapters`.`id`)))
          JOIN `iit_commentary_headers`
            ON ((`iit_commentary_headers`.`commentary_id` = `iit_commentary_index`.`commentary_id`))
        WHERE (`books`.`book_title` = "Isaiah IIT"
          AND `chapters`.`chapter_number` = ?)
        GROUP BY `commentary_id`';

        $results = DB::select($sql, array($chapter_number));

        return $results;
    }

    /**
     * Get the commentary subject verses
     *
     * @param $commentary_id
     * @return string
     */
    private function _getCommentarySubjectVerses($commentary_id) {
        $sql = 'SELECT
          `iit_commentary`.`subject_verses` AS subject_verses
        FROM (isaiahde_logos.iit_commentary_index
          JOIN iit_commentary
            ON (`iit_commentary_index`.`commentary_id` = `iit_commentary`.`id`))
        WHERE (`iit_commentary_index`.`commentary_id` = ?)';

        $results = DB::select($sql, array($commentary_id));

        if(!empty($results)) {
            return $results[0]->subject_verses;
        } else {
            return false;
        }

        return $results;
    }

    /**
     * Get the commentary verses
     *
     * @param $commentary_id
     * @return array
     */
    private function _getCommentaryVerses($commentary_id) {
        $sql = 'SELECT
          `iit_commentary_index`.`verse_id` AS `verse_id`,
          `iit_commentary_index`.`verse_number` AS `verse_number`
        FROM isaiahde_logos.iit_commentary_index
        WHERE (`iit_commentary_index`.`commentary_id` = ?)';

        $results = DB::select($sql, array($commentary_id));

        /*if(!empty($results)) {
            return $results[0];
        } else {
            return false;
        }*/

        return $results;
    }

    /**
     * Get the concordance verse citations
     *
     * @param $verse_id
     * @return array
     */
    private function _getConcordanceVerse($verse_id) {
        $sql = 'SELECT
          `iit_concordance_words`.`word` AS word,
          `iit_concordance_citations`.`url` AS url,
          `iit_concordance_citations`.`segment_id` AS segment_id,
          `iit_concordance_citations`.`sub_segment_id` AS sub_segment_id
        FROM (`isaiahde_logos`.`iit_concordance_citations`
          JOIN `iit_concordance_words`
            ON (`iit_concordance_citations`.`concordance_id` = `iit_concordance_words`.`id`))
        WHERE (verse_id = ?)';

        $results = DB::select($sql, array($verse_id));

        return $results;
    }

    /**
     * Get the header's commentary
     *
     * @param int $commentary_id
     * @return array
     */
    private function _getCommentary($commentary_id) {
        $sql = 'SELECT
          `iit_commentary`.`id` AS commentary_id,
          `iit_commentary`.`commentary` AS commentary,
          `iit_commentary`.`subject_verses` AS subject_verses
        FROM isaiahde_logos.iit_commentary
        WHERE (`iit_commentary`.`id` = ?)';

        $results = DB::select($sql, array($commentary_id));

        if(!empty($results)) {
            return $results[0]->commentary;
        } else {
            return false;
        }

        return $results;
    }

    /**
     * @param $scripture_text
     * @param $verse_id
     * @param $chapter_keywords_html
     * @param $section_tab
     * @return array
     */
    private function _buildKeywordsHTML($scripture_text, $verse_id, $chapter_keywords_html, $section_tab)
    {
        $has_keyword = preg_match('/<b>.*<\/b>/U', $scripture_text);
        if ($has_keyword == true) {
            $iit_keywords = $this->getIITKeyword($verse_id);
            if(!empty($iit_keywords)) {
                $keywords_found = array();
                foreach ($iit_keywords as $iit_keyword) {
                    $keyword_id = $iit_keyword->keyword_id;
                    $keyword = $iit_keyword->keyword;
                    $keywords_found[] = $keyword;
                    $keywords_count = array_count_values($keywords_found);
                    $identical_keywords = $keywords_count[$keyword];
                    $color = $iit_keyword->color_name;
                    $section = '';
                    if ($section_tab == 'one-col') {
                        $section = 'one_col';
                    } elseif ($section_tab == 'three-col') {
                        $section = 'three_col';
                    } elseif ($section_tab == 'commentary') {
                        $section = 'commentary';
                    } elseif ($section_tab == 'concordance') {
                        $section = 'concordance';
                    }
                    //$scripture_text = $this->_pregReplaceNth($pattern, $replacement, $scripture_text, $segment_id);
                    $pattern = '/<b>(' . $keyword . ')<\/b>/Ui';
                    $replacement = '<b><a id="' . $keyword_id . '_' . $section . '_keyword_verse" name="' . $section . '" href="#defmodal" class="modal-trigger keyword-modal def-trigger ' . $color . '" data-toggle="modal">$1</a></b>';
                    $scripture_text = $this->_pregReplaceNth($pattern, $replacement, $scripture_text, $identical_keywords);
                    $chapter_keywords_html .= '<div id="' . $keyword_id . '_' . $section . '_keyword_description" name="' . $section . '" style="display: none;">' . html_entity_decode($iit_keyword->keyword_description) . '</div>';
                    $chapter_keywords_html .= '<div id="' . $keyword_id . '_' . $section . '_keyword_color" name="' . $section . '" style="display: none;">' . $color . '</div>';
                }
                return array($scripture_text, $chapter_keywords_html);
            } else {
                $test = 1;
                return array($scripture_text, $chapter_keywords_html);
            }
        }
        return array($scripture_text, $chapter_keywords_html);
    }
}