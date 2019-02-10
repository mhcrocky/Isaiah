<?php
    require('../../includes/amysql/AMysql.php');
    
    $amysql = new AMysql(array (
        'host' => '10.211.55.4',
        'username' => 'tekton',
        'password' => 'kkg7n5a4',
        'db'        => 'isaiahexplained',
    ));
    
    $book_title = 'Isaiah IIT';
    
    // Get Book ID
    $book_id = get_book_id($book_title, $amysql);
    
    // Get book chapters
    $book_chapter_count = get_book_chapter_count($book_id, $amysql);
    
    // Insert chapters if none found
    if(empty($book_chapter_count)) {
        insert_isaiah_chapters($book_id, $amysql);
    }
    
    // Display chapter count
    $book_chapter_count = get_book_chapter_count($book_id, $amysql);
    
    echo $book_chapter_count;
    
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
?>
