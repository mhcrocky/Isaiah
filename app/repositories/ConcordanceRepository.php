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

    public function GetConcordanceMetaTags() {
        $meta_tags = [];
        $common_meta_tags = MetaTags::where('route', '=', '*')->get();
        foreach($common_meta_tags as $common_meta_tag) {
            if($common_meta_tag->name != 'robots') {
                $meta_tags[] = ['name' => $common_meta_tag->name, 'content' => $common_meta_tag->content];
            } else {
                $query_string = Input::getQueryString();
                if(!empty($query_string)) {
                    $meta_tags[] = ['name' => $common_meta_tag->name, 'content' => 'noindex, nofollow'];
                } else {
                    $meta_tags[] = ['name' => $common_meta_tag->name, 'content' => $common_meta_tag->content];
                }
            }
        }
        $specific_meta_tags = MetaTags::where('route', '=', "/concordance")->get();
        foreach($specific_meta_tags as $specific_meta_tag) {
            $meta_tags[] = ['name' => $specific_meta_tag->name, 'content' => $specific_meta_tag->content];
        }
        $meta = '';
        foreach($meta_tags as $meta_tag) {
            $meta .= <<<EOT
<meta name="{$meta_tag['name']}" content="{$meta_tag['content']}">

EOT;
        }
        return $meta;
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
            $app_url = Config::get('app.url');
            $concordance_html .= <<<EOT
<p id={$word_citation->url} class="ref"><a href="{$app_url}/{$word_citation->chapter}?citation={$word_citation->url}#concordance" class="verse">{$word_citation->subject_verse}</a> <span class="vtext">{$word_citation->citation}</span></p>$EOL
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