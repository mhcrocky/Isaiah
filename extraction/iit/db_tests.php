<?php
    require('../../includes/amysql/AMysql.php');
    
    $amysql = new AMysql(array (
        'host' => '10.211.55.4',
        'username' => 'tekton',
        'password' => 'kkg7n5a4',
        'db'        => 'isaiahexplained',
    ));
    
    $amysql->setUtf8();
    
    //$results = get_book_chapter_verse('Isaiah IIT', 1, 1, $amysql);
    
    /*
    $book_title = 'Isaiah IIT';
    $chapter_number = 1;
    
    $chapter_id = get_book_chapter_id($book_title, $chapter_number, $amysql);
    
    $verse_number = 1;
    $verse_text = 'This is a test';
    $segment_id = 1;
    $is_poetry = false;
    
    $verse_id = insert_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry, $amysql);
    
    $chapter_verse_text = get_book_chapter_verse($book_title, $chapter_number, $verse_number, $amysql);
    
    $success = update_chapter_verse($chapter_id, $verse_number, 'This is an update test', 2, true, $amysql);
    
    $chapter_verse_text = get_book_chapter_verse($book_title, $chapter_number, $verse_number, $amysql);
    
    $success = delete_chapter_verse_id($verse_id, $amysql);
    
    $verse_id = insert_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry, $amysql);
    
    $success = delete_chapter_verse_number($chapter_id, $verse_number, $amysql);
    
    $test = 1;*/
    
    /*
    //$book_title = 'Isaiah IIT';
    //$book_title = 'Isaiah Hebrew';
    $book_title = 'Isaiah KJV';
    
    // Get Book ID
    $book_id = get_book_id($book_title, $amysql);
    
    // Get book chapters
    $book_chapter_count = get_book_chapter_count($book_id, $amysql);
    
    echo 'Start count: ' . $book_chapter_count . '<br>';
    
    // Insert chapters if none found
    if(empty($book_chapter_count)) {
        insert_isaiah_chapters($book_id, $amysql);
    }
    
    // Display chapter count
    $book_chapter_count = get_book_chapter_count($book_id, $amysql);
    
    echo 'End count: ' . $book_chapter_count;*/
    
    function get_book_id($book_title, $amysql) {
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
    
    function get_book_chapter_count($book_id, $amysql) {
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
    * Get Book Chapter/Verse
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param int $verse_number
    * @param AMysql $amysql
    */
    function get_book_chapter_verse($book_title, $chapter_number, $verse_number, $amysql) {
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
    
    /**
    * Get IIT Book Chapter/Verse
    * 
    * @param string $book_title
    * @param int $chapter_number
    * @param AMysql $amysql
    */
    function get_book_chapter_id($book_title, $chapter_number, $amysql) {
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
    
    function insert_isaiah_chapters($book_id, $amysql) {
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
    * @param AMysql $amysql
    */
    function insert_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry, $amysql) {
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
    * Update Book Chapter/Verse
    * 
    * @param int $chapter_id
    * @param int $verse_number
    * @param string $verse_text
    * @param int $segment_id
    * @param bool $is_poetry
    * @param AMysql $amysql
    */
    function update_chapter_verse($chapter_id, $verse_number, $verse_text, $segment_id, $is_poetry, $amysql) {
        $table_name = 'verses';
        
        $data = array(
            'verse_number' => $verse_number,
            'scripture_text' => $verse_text,
            'segment_id' => $segment_id,
            'is_poetry' => $is_poetry
        );
        
        $success = $amysql->update($table_name, $data, 'chapter_id = :chapter_id AND verse_number = :verse_number', array('chapter_id' => $chapter_id, 'verse_number' => $verse_number));
        //$affectedRows = $amysql->lastStatement->affectedRows();
        
        return $success;
    }
    
    /**
    * Delete chapter verse by ID
    * 
    * @param int $chapter_id
    * @param int $verse_id
    * @param AMysql $amysql
    */
    function delete_chapter_verse_id($verse_id, $amysql) {
        $table_name = 'verses';
        $where = 'id = ?';
        $success = $amysql->delete($table_name, $where, array ($verse_id));
        
        return $success;
    }
    
    /**
    * Delete chapter verse by ID
    * 
    * @param int $chapter_id
    * @param int $verse_id
    * @param AMysql $amysql
    */
    function delete_chapter_verse_number($chapter_id, $verse_number, $amysql) {
        $table_name = 'verses';
        $where = 'chapter_id = :chapter_id AND verse_number = :verse_number';
        $success = $amysql->delete($table_name, $where, array ('chapter_id' => $chapter_id, 'verse_number' => $verse_number));
        
        return $success;
    }
?>
