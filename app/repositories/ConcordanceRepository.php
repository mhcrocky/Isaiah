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

        $word_citations = $this->_getLetterCitations($concordance_letter);
        $words = array();

        foreach($word_citations as $word_citation) {
            if(!in_array($word_citation->word, $words)) {
                $words[] = $word_citation->word;
                $concordance_html .= <<<EOT
<h4 id="{$word_citation->word}">{$word_citation->word}</h4>$EOL
EOT;
            }
            $concordance_html .= <<<EOT
<p id={$word_citation->url} class="ref"><a href="/{$word_citation->chapter}?citation={$word_citation->url}#concordance" class="verse">{$word_citation->subject_verse}</a> <span class="vtext">{$word_citation->citation}</span></p>$EOL
EOT;
        }

        return html_entity_decode($concordance_html);
    }

    private function _getLetterCitations($letter) {
        $sql = 'SELECT
          `iit_concordance_words`.`word` AS word,
          `iit_concordance_citations`.`url` AS url,
          `iit_concordance_citations`.`subject_verse` AS subject_verse,
          `iit_concordance_citations`.`chapter` AS chapter,
          `iit_concordance_citations`.`citation` AS citation
        FROM (`isaiahde_logos`.`iit_concordance_words`
          JOIN `isaiahde_logos`.`iit_concordance_citations`
            ON (`iit_concordance_words`.`id` = `iit_concordance_citations`.`concordance_id`)
          JOIN `isaiahde_logos`.`iit_concordance_index`
            ON (`iit_concordance_index`.`citation_id` = `iit_concordance_citations`.`id`))
        WHERE (`iit_concordance_index`.`is_displayed` = TRUE AND `iit_concordance_words`.`letter` = ?)';

        $results = DB::select($sql, array($letter));

        return $results;
    }
} 