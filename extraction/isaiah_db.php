<?php
    require('lib/amysql-master/AMysql.php');
    
    /**
    * Get AMysql instance
    * 
    */
    function get_amysql() {
        /*$amysql = new AMysql(array (
            'host' => '10.211.55.4',
            'username' => 'tekton',
            'password' => 'kkg7n5a4',
            'db'        => 'isaiahexplained',
        ));*/
        
        $amysql = new AMysql(array (
            'host' => '127.0.0.1',
            'port' => '33060',
            'username' => 'homestead',
            'password' => 'secret',
            'db'        => 'isaiahde_logos',
        ));
        
        $amysql->connect();
        $amysql->setUtf8();
        
        return $amysql;
    }
    
    /**
    * Get book ID
    * 
    * @param string $book_title
    * @return int
    */
    function get_book_id($book_title) {
        $amysql = get_amysql();
        $table_name = 'books';
        
        $select = $amysql->select();
        $select
            ->column('id')
            ->from($table_name)
            ->whereBind('book_title = :book_title', 'book_title', $book_title);
        $select->execute();
        $row = $select->fetch();
        
        if(!empty($row['id'])) {
            $book_id = $row['id'];
        } else {
            $book_id = 0;
        }
        
        return $book_id;  
    }
    
    /**
    * Get book chapter count
    * 
    * @param int $book_id
    * @return int
    */
    function get_book_chapter_count($book_id) {
        $amysql = get_amysql();
        $table_name = 'chapters';
        
        $select = $amysql->select();
        $select
            ->column('id')
            ->from($table_name)
            ->whereBind('book_id = :book_id', 'book_id', $book_id);
        $select->execute();
        $rows = $select->fetchAll();
        if(!empty($rows)) {
            $chapter_count = count($rows);
        } else {
            $chapter_count = 0;
        }
        
        return $chapter_count;
    }
    
    /**
    * Get IIT Book Chapter/Verse
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param AMysql $amysql
    * @return int
    */
    function get_book_chapter_id($book_title, $chapter_number, &$amysql) {
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $chapter_number
        );
        
        $sql = 'SELECT 
                  `chapters`.`id` AS `chapter_id` 
                FROM (((`volumes` 
                  JOIN `books` 
                    ON ((`books`.`volume_id` = `volumes`.`id`))) 
                  JOIN `chapters` 
                    ON ((`chapters`.`book_id` = `books`.`id`)))) 
                WHERE (`books`.`book_title` = :book_title 
                  AND `chapters`.`chapter_number` = :chapter_number) 
                ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $row = $stmt->fetch();
        
        return (int) $row['chapter_id'];
    }
    
    /**
    * Get IIT Book Chapter/Verse
    * 
    * @param string $book_title
    * @param int $book_chapter
    * @param int $book_verse
    * @param AMysql $amysql
    * @return int
    */
    function get_book_verse_id($book_title, $book_chapter, $book_verse, &$amysql) {
        //$amysql = get_amysql();
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $book_chapter,
            'book_verse' => $book_verse
        );
        
        $sql = 'SELECT                   
          `verses`.`id` AS `verse_id`
        FROM (((`volumes`
          JOIN `books`
            ON ((`books`.`volume_id` = `volumes`.`id`)))
          JOIN `chapters`
            ON ((`chapters`.`book_id` = `books`.`id`)))
          JOIN `verses`
            ON ((`verses`.`chapter_id` = `chapters`.`id`)))
        WHERE (`books`.`book_title` = :book_title 
          AND `chapters`.`chapter_number` = :chapter_number 
          AND `verses`.verse_number = :book_verse)
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `verses`.`id`';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $row = $stmt->fetch();
        
        return (int) $row['verse_id'];
    }
    
    /**
    * Get chapter header
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @return string
    */
    function get_book_chapter_header($book_title, $chapter_number) {
        $amysql = get_amysql();
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $chapter_number
        );
        
        $sql = 'SELECT
          `iit_headings`.`heading_text` AS `heading_text`
        FROM (((`volumes`
          JOIN `books`
            ON ((`books`.`volume_id` = `volumes`.`id`)))
          JOIN `chapters`
            ON ((`chapters`.`book_id` = `books`.`id`)))
          JOIN `iit_headings`
            ON ((`chapters`.`id` = `iit_headings`.chapter_id)))
        WHERE (`books`.`book_title` = :book_title  
          AND `chapters`.`chapter_number` = :chapter_number)
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`;';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $results = $stmt->fetchAssoc();
        
        return $results['heading_text'];
    }
    
    /**
    * Get book chapter text 
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param AMysql $amysql
    * @return array
    */
    function get_book_chapter($book_title, $chapter_number, &$amysql) {
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $chapter_number
        );
        
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
        WHERE (`books`.`book_title` = :book_title 
          AND `chapters`.`chapter_number` = :chapter_number) 
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `verses`.`id`';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $results = $stmt->fetchAllAssoc();
        
        return $results;
    }
    
    /**
    * Get verse
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param int $verse_number
    * @return array
    */
    function get_book_chapter_verse($book_title, $chapter_number, $verse_number) {
        $amysql = get_amysql();
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $chapter_number,
            'verse_number' => $verse_number
        );
        
        $sql = 'SELECT 
          `chapters`.`id` AS `chapter_id`, 
          `verses`.`id` AS `verse_id`, 
          `chapters`.`chapter_number` AS `chapter_number`, 
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
        WHERE (`books`.`book_title` = :book_title 
          AND `chapters`.`chapter_number` = :chapter_number 
          AND `verses`.verse_number = :verse_number) 
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `verses`.`id`';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $results = $stmt->fetchAllAssoc();
        
        return $results;
    }
    
    /**
    * Get keyword
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param int $verse_number
    * @return array
    */
    function get_book_chapter_verse_keyword($book_title, $chapter_number, $verse_number) {
        $amysql = get_amysql();
        $binds = array(
            'book_title' => $book_title,
            'chapter_number' => $chapter_number,
            'verse_number' => $verse_number
        );
        
        $sql = 'SELECT 
          `chapters`.`id` AS `chapter_id`, 
          `verses`.`id` AS `verse_id`, 
          `chapters`.`chapter_number` AS `chapter_number`, 
          `verses`.`verse_number` AS `verse_number`, 
          `verses`.`scripture_text` AS `scripture_text`, 
          `verses`.`segment_id` AS `segment_id`, 
          `verses`.`is_poetry` AS `is_poetry` 
        FROM (((`volumes` 
          JOIN `books` 
            ON ((`books`.`volume_id` = `volumes`.`id`))) 
          JOIN `chapters` 
            ON ((`chapters`.`book_id` = `books`.`id`))) 
          JOIN `verses` 
            ON ((`verses`.`chapter_id` = `chapters`.`id`))) 
        WHERE (`books`.`book_title` = :book_title 
          AND `chapters`.`chapter_number` = :chapter_number 
          AND `verses`.verse_number = :verse_number) 
        ORDER BY `volumes`.`id`, `books`.`id`, `chapters`.`id`, `verses`.`id`';
        
        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $results = $stmt->fetchAllAssoc();
        
        return $results;
    }

    function getIITKeyword($verse_id, &$amysql) {
        $binds = array(
            'verse_id' => $verse_id
        );

        $sql = 'SELECT id as keyword_id, color_name, keyword, keyword_description
                FROM iit_keywords
                WHERE verse_id = :verse_id';

        $stmt = $amysql->prepare($sql);
        $stmt->execute($binds);
        $results = $stmt->fetchAllAssoc();

        if(!empty($results)) {
            return $results;
        } else {
            return NULL;
        }
    }
    
    /**
    * Insert Isaiah chapters 1-66 for provided book_id
    * 
    * @param int $book_id
    */
    function insert_isaiah_chapters($book_id) {
        $amysql = get_amysql();
        $table_name = 'chapters';
        
        for($i = 1; $i <= 66; $i++) {
            $data = array (
                'book_id' => $book_id,
                'chapter_number' => $i
            );
            $amysql->insert($table_name, $data);
        }
    }
    
    /**
    * Insert Book Chapter/Verse
    * 
    * @param int $chapter_id
    * @param int $verse_number
    * @param string $verse_text
    * @param int $segment_id
    * @param bool $is_poetry
    * @return int
    */
    function insert_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry) {
        $amysql = get_amysql();
        $table_name = 'verses';
        
        $data = array(
            'chapter_id' => $chapter_id,
            'verse_number' => $verse_number,
            'scripture_text' => $verse_text,
            'segment_id' => $segment_id,
            'is_poetry' => $is_poetry
        );
        
        $id = $amysql->insert($table_name, $data);
        
        return $id;
    }
    
    /**
    * Insert Verse Keyword
    * 
    * @param int $verse_id
    * @param string $color_name
    * @param string $color_value
    * @param string $keyword
    * @param string $keyword_description
    * @param AMysql $amysql
    * @return int
    */
    function insert_verse_keyword($verse_id, $color_name, $color_value, $keyword, $keyword_description, &$amysql) {
        //$amysql = get_amysql();
        $table_name = 'iit_keywords';
        
        $data = array(
            'verse_id' => $verse_id,
            'color_name' => $color_name,
            'color_value' => $color_value,
            'keyword' => $keyword,
            'keyword_description' => $keyword_description
        );
        
        $id = $amysql->insert($table_name, $data);
        
        return $id;
    }

    /**
     * Insert Commentary Header
     *
     * @param int $commentary_id
     * @param string $header
     * @param AMysql $amysql
     * @return int
     */
    function insert_commentary_header($commentary_id, $header, &$amysql) {
        $data = array(
            'commentary_id' => $commentary_id,
            'header' => $header
        );

        $id = $amysql->insert('iit_commentary_headers', $data);

        return $id;
    }

    /**
     * Insert Concordance
     *
     * @param string $word
     * @param string $letter
     * @param AMysql $amysql
     * @return int
     */
    function insert_concordance($word, $letter, &$amysql) {
        $data = array(
            'word' => $word,
            'letter' => $letter
        );

        $id = $amysql->insert('iit_concordance_words', $data);

        return $id;
    }

    /**
     * Insert Concordance Citation
     *
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
     * @param AMysql $amysql
     * @return int
     */
    function insert_concordance_citation($concordance_id, $chapter_id, $verse_id, $chapter_number, $url,
                                         $subject_verse, $letter, $citation, $segment_id, $sub_segment_id, &$amysql) {
        $data = array(
            'concordance_id' => $concordance_id,
            'chapter_id' => $chapter_id,
            'verse_id' => $verse_id,
            'chapter' => $chapter_number,
            'url' => $url,
            'subject_verse' => $subject_verse,
            'letter' => $letter,
            'citation' => $citation,
            'segment_id' => $segment_id,
            'sub_segment_id' => $sub_segment_id
        );

        $id = $amysql->insert('iit_concordance_citations', $data);

        return $id;
    }

    /**
     * Insert Concordance Index
     *
     * @param int $concordance_id
     * @param int $citation_id
     * @param bool $is_displayed
     * @param AMysql $amysql
     * @return int
     */
    function insert_concordance_index($concordance_id, $citation_id, $is_displayed, &$amysql) {
        $data = array(
            'concordance_id' => $concordance_id,
            'citation_id' => $citation_id,
            'is_displayed' => $is_displayed
        );

        $id = $amysql->insert('iit_concordance_index', $data);

        return $id;
    }

    /**
     * Insert Commentary
     *
     * @param string $commentary_html
     * @param string $subject_verses
     * @param AMysql $amysql
     * @return int
     */
    function insert_commentary($commentary_html, $subject_verses, &$amysql) {
        $data = array(
            'commentary' => $commentary_html,
            'subject_verses' => $subject_verses
        );

        $id = $amysql->insert('iit_commentary', $data);

        return $id;
    }

    /**
     * Update Commentary
     *
     * @param int $commentary_id
     * @param string $commentary_html
     * @param string $subject_verses
     * @param AMysql $amysql
     * @return bool
     */
    function update_commentary($commentary_id, $commentary_html, $subject_verses = '', &$amysql) {
        if(!empty($subject_verses)) {
            $data = array(
                'commentary' => $commentary_html,
                'subject_verses' => $subject_verses
            );
        } else {
            $data = array(
                'commentary' => $commentary_html
            );
        }

        $success = $amysql->update('iit_commentary', $data, 'commentary_id = :commentary_id', array('commentary_id' => $commentary_id));
        //$affectedRows = $amysql->lastStatement->affectedRows();

        return $success;
    }

    /**
     * Insert Commentary Index
     *
     * @param int $chapter_id
     * @param int $verse_id
     * @param float $verse_number
     * @param int $commentary_id
     * @param AMysql $amysql
     * @return int
     */
    function insert_commentary_index($chapter_id, $verse_id, $verse_number, $commentary_id, &$amysql) {
        $data = array(
            'chapter_id' => $chapter_id,
            'verse_id' => $verse_id,
            'verse_number' => $verse_number,
            'commentary_id' => $commentary_id
        );

        $id = $amysql->insert('iit_commentary_index', $data);

        return $id;
    }
    
    /**
    * Update Book Chapter/Verse
    * 
    * @param int $chapter_id
    * @param int $verse_number
    * @param string $verse_text
    * @param int $segment_id
    * @param bool $is_poetry
    * @param AMysql $amysql
    * @return bool
    */
    function update_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry, &$amysql) {
        $data = array(
            'verse_number' => $verse_number,
            'scripture_text' => $verse_text,
            'segment_id' => $segment_id,
            'is_poetry' => $is_poetry
        );
        
        $success = $amysql->update('verses', $data, 'chapter_id = :chapter_id AND verse_number = :verse_number', array('chapter_id' => $chapter_id, 'verse_number' => $verse_number));
        //$affectedRows = $amysql->lastStatement->affectedRows();
        
        return $success;
    }
    
    /**
    * Delete chapter verse by ID
    *
    * @param int $verse_id
    * @param AMysql $amysql
    * @return bool
    */
    function delete_chapter_verse_id($verse_id, &$amysql) {
        $table_name = 'verses';
        $where = 'id = ?';
        $success = $amysql->delete($table_name, $where, array ($verse_id));
        
        return $success;
    }
    
    /**
    * Delete chapter verse by ID
    * 
    * @param int $chapter_id
    * @param int $verse_number
    * @param AMysql $amysql
    * @return bool
    */
    function delete_chapter_verse_number($chapter_id, $verse_number, &$amysql) {
        $table_name = 'verses';
        $where = 'chapter_id = :chapter_id AND verse_number = :verse_number';
        $success = $amysql->delete($table_name, $where, array ('chapter_id' => $chapter_id, 'verse_number' => $verse_number));
        
        return $success;
    }

    /**
    * Strip a string from the end of a string
    * 
    * @param mixed $message the input string
    * @param mixed $strip string to remove
    * 
    * @return string the modified string
    */
    function strrtrim($message, $strip) {
        $lines = explode($strip, $message); 
        $last  = '';
        do { 
            $last = array_pop($lines); 
        } while (empty($last) && (count($lines))); 
        return implode($strip, array_merge($lines, array($last))); 
}
?>
