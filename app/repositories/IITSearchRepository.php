<?php

class IITSearchRepository {
    /**
     * Gets html for the chapter index
     *
     * @return string Chapter selection widget html
     */
    public static function GetIITSearchIndex() {
        return Heading::all();
    }

    /**
     * @param $search_term
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function GetIITSearchTerm($search_term, $page = 1, $limit = 20) {
        $result['count'] = 0;
        $result['html'] = '';
        $result_count = 0;
        $search_html = '';
        $EOL = PHP_EOL;

        $is_exact_match = preg_match('/^".*"$/', $search_term);
        if($is_exact_match == true) {
            $results = $this->_getIITSearchResults(preg_replace('/"/', '' , $search_term), true, $page, $limit);
        } else {
            $results = $this->_getIITSearchResults($search_term, false, $page, $limit);
        }

        //$search_term = preg_replace('/"/', '' , $search_term);
        $search_results = $results['results'];
        $total_count = $results['count'];

        if(!empty($search_results)) {
            $result_count = count($search_results);

            $ol_start = (($page - 1) * $limit) + 1;

            $search_html = <<<EOT
<ol start="$ol_start">$EOL
EOT;

            for ($i = 0; $i < $result_count; $i++) {
                $chapter_number = $search_results[$i]->chapter_number;
                $verse_number = $this->_strrtrim($search_results[$i]->verse_number, '.0');
                $scripture_text = html_entity_decode($search_results[$i]->scripture_text_plain);
                $scripture_text = preg_replace('/<span\b[^>]*>(.*)<\/span>/U', ' $1 ', $scripture_text);
                $scripture_text = preg_replace('/\s\s/', ' ', $scripture_text);
                $scripture_text = preg_replace('/<b>|<\/b>/', '', $scripture_text);
                $search_html .= <<<EOT
<li><a href='/${chapter_number}?verse=$verse_number&search=$search_term#one_col'><i>Isaiah ${chapter_number}:${verse_number}</i></a> <span>${scripture_text}</span></li>
EOT;
            }

            $search_html .= <<<EOT
</ol>$EOL
EOT;
        }

        $result['count'] = $total_count;
        $result['html'] = $search_html;
        $result['paginator'] = Paginator::make($search_results, $total_count, $limit);

        return $result;
    }

    /**
     * Get the concordance words for specified letter
     *
     * @param string $search_term
     * @param bool $is_exact
     * @param int $page
     * @param int $limit
     * @return array
     */
    private function _getIITSearchResults($search_term, $is_exact = false, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $results = DB::table('volumes')
            ->limit($limit)
            ->offset($offset)
            /*->orderBy('volumes.id')
            ->orderBy('books.id')
            ->orderBy('chapters.id')
            ->orderBy('verses.id')*/
            ->join('books', function($join)
            {
                $join->on('volumes.id', '=', 'books.volume_id');
            })
            ->join('chapters', function($join)
            {
                $join->on('books.id', '=', 'chapters.book_id');
            })
            ->join('verses', function($join)
            {
                $join->on('chapters.id', '=', 'verses.chapter_id');
            })
            ->where('books.book_title', '=', 'Isaiah IIT')
            ->where(function($query) use ($search_term, $is_exact) {
                if($is_exact == true) {
                    $query->where('verses.scripture_text_plain', 'RLIKE', '[[:<:]]' . $search_term . '[[:>:]]', 'and');
                } else {
                    $search_term = preg_replace('/ /', '%', $search_term);
                    $query->where('verses.scripture_text_plain', 'LIKE', '%' . $search_term . '%', 'and');
                }
            })
            ->select('chapters.id as chapter_id', 'verses.id as verse_id', 'chapters.chapter_number',
                'verses.verse_number', 'verses.scripture_text_plain', 'verses.segment_id',
                'verses.is_poetry', 'verses.one_col_html',  'verses.three_col_html')->get();
        $count_result = DB::table('volumes')
            /*->orderBy('volumes.id')
            ->orderBy('books.id')
            ->orderBy('chapters.id')
            ->orderBy('verses.id')*/
            ->join('books', function($join)
            {
                $join->on('volumes.id', '=', 'books.volume_id');
            })
            ->join('chapters', function($join)
            {
                $join->on('books.id', '=', 'chapters.book_id');
            })
            ->join('verses', function($join)
            {
                $join->on('chapters.id', '=', 'verses.chapter_id');
            })
            ->where('books.book_title', '=', 'Isaiah IIT')
            ->where(function($query) use ($search_term, $is_exact) {
                if($is_exact == true) {
                    $query->where('verses.scripture_text_plain', 'RLIKE', '[[:<:]]' . $search_term . '[[:>:]]', 'and');
                } else {
                    $search_term = preg_replace('/ /', '%', $search_term);
                    $query->where('verses.scripture_text_plain', 'LIKE', '%' . $search_term . '%', 'and');
                }
            })
            ->select(array(DB::raw('COUNT(chapters.id) as results')))->get();

        return array('results' => $results, 'count' => $count_result[0]->results);

        /*$queries = DB::getQueryLog();
        $last_query = end($queries);
        echo $last_query['query'];
        dd($last_query);*/
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
} 