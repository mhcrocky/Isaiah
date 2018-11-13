<?php

class ConcordanceRepository {
    /**
     * Gets html for the chapter index
     *
     * @return string Chapter selection widget html
     */
    public static function GetConcordanceIndex() {
        return Heading::all();
    }

    public function GetConcordanceLetterList($concordance_letter) {
        $concordance_html = '';
        $EOL = PHP_EOL;
        $word_list = $this->_getConcordanceLetterWords($concordance_letter);
        foreach($word_list as $letter_word) {
            $concordance_id = $letter_word->concordance_id;
            $word = $letter_word->word;
            $word_citations = $this->_getWordCitations($concordance_id);
            $concordance_html .= <<<EOT
<h4 id="${word}">${word}</h4>$EOL
EOT;

            foreach($word_citations as $word_citation) {
                $url = $word_citation->url;
                $subject_verse = $word_citation->subject_verse;
                $chapter = $word_citation->chapter;
                $citation = $word_citation->citation;
                $concordance_html .= <<<EOT
<p id=${url} class="ref"><a href="/{$chapter}?citation=${url}#concordance" class="verse">${subject_verse}</a> <span class="vtext">${citation}</span></p>$EOL
EOT;

            }
        }
        return $concordance_html;
    }

    /**
     * Get the concordance words for specified letter
     *
     * @param string $letter
     * @return array
     */
    private function _getConcordanceLetterWords($letter) {
        $sql = 'SELECT
          `iit_concordance_words`.`id` AS concordance_id,
          `iit_concordance_words`.`word` AS word
        FROM `isaiahde_logos`.`iit_concordance_words`
        WHERE (`iit_concordance_words`.`letter` = ?)';

        $results = DB::select($sql, array($letter));

        return $results;
    }

    /**
     * Get the concordance words for specified letter
     *
     * @param int $concordance_id
     * @return array
     */
    private function _getWordCitations($concordance_id) {
        $test = 1;

        $sql = 'SELECT
          `iit_concordance_citations`.`url` AS url,
          `iit_concordance_citations`.`subject_verse` AS subject_verse,
          `iit_concordance_citations`.`chapter` AS chapter,
          `iit_concordance_citations`.`citation` AS citation
        FROM (`isaiahde_logos`.`iit_concordance_citations`
          JOIN `isaiahde_logos`.`iit_concordance_index`
            ON (`iit_concordance_index`.`citation_id` = `iit_concordance_citations`.`id`))
        WHERE (`iit_concordance_index`.`is_displayed` = TRUE AND `iit_concordance_citations`.`concordance_id` = ?)';

        /*$sql = 'SELECT
          `iit_concordance_citations`.`url` AS url,
          `iit_concordance_citations`.`subject_verse` AS subject_verse,
          `iit_concordance_citations`.`chapter` AS chapter,
          `iit_concordance_citations`.`citation` AS citation
        FROM `isaiahde_logos`.`iit_concordance_citations`
        WHERE (`iit_concordance_citations`.`concordance_id` = ?)';*/

        $results = DB::select($sql, array($concordance_id));

        return $results;
    }
} 