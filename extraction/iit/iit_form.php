<?php
    ini_set('memory_limit', '512M');
    session_start();
    require('../../includes/simplehtmldom_1_5/simple_html_dom.php');
    require('isaiah_db.php');
    if($_SERVER['REQUEST_METHOD']== "POST") {
        $message = 'posted';
        $segment_id = 1;
        $book_title = 'Isaiah IIT';
        if(!empty($_POST['chapter'])) {
            $selected_chapter = (int) $_POST['chapter'];
            if(!empty($_SESSION['selected_chapter'])) {
                if($selected_chapter != $_SESSION['selected_chapter']) {
                    $segment_id = 1;
                    $_SESSION['segment_id'] = $segment_id;
                } else {
                    if(!empty($_POST['segment_id'])) {
                        $segment_id = $_POST['segment_id'];
                        $_SESSION['segment_id'] = $segment_id;
                    } else {
                        $_SESSION['segment_id']++;
                        $segment_id = $_SESSION['segment_id'];
                    }
                }
            }
            $_SESSION['selected_chapter'] = $selected_chapter;
        } else {
            if(!empty($_SESSION['selected_chapter'])) {
                $selected_chapter = $_SESSION['selected_chapter'];
            } else {
                $selected_chapter = 1; 
            }
        }
        if(!empty($_POST['section_type'])) {
            $selected_section = $_POST['section_type'];
        } else {
            if(!empty($_SESSION['section_type'])) {
                $selected_section = $_SESSION['section_type'];
            } else {
                $selected_section = "poetry"; 
            }
        }
        $is_poetry = false;
        switch($selected_section) {
            case 'poetry':
                $is_poetry = true;
                break;
            case 'prose':
            default:
                $is_poetry = false;
                break;
        }
        if(!empty($_POST['section_text'])) {
            $section_text = $_POST['section_text'];
        } else {
            $message = 'Error: Section text is empty.';
        }
        // TODO: count segments
        if(!empty($section_text)) {
            $html = str_get_html($section_text);
            $verses = array();
            $verse_number = 0;
            $error = '';
            foreach($html->find('p[class=western]') as $iitHTML) {
                $iitInnerHTML = $iitHTML->innertext;
                $space_count = 0;
                // Filtering
                $section_text = strip_tags($iitInnerHTML, '<strong><em><b><i><sup><br>');
                $section_text = preg_replace('/\r\n/', ' ', $section_text);
                $section_text = preg_replace('/\t+/', '', $section_text);
                preg_match('/<sup>\w<\/sup>/', $section_text, $matches, PREG_OFFSET_CAPTURE);
                if(!empty($matches)) {
                    $section_text = preg_replace('/(.*)<sup>(<i><sup>\w<\/sup><\/i>)<\/sup>(.*)/s', '\1\2\3', $section_text);
                }
                $is_matched = preg_match('/^\s+(\d*)/', $section_text, $matches, PREG_OFFSET_CAPTURE);
                if(!empty($is_matched)) {
                    $space_count = strspn($section_text, ' ');
                } else {
                    $space_count = 0;
                }
                $section_text = trim($section_text);
                // Verse(s) extraction
                $pattern = '/(\d*)\s*(\D+)/s';
                $is_matched = preg_match_all($pattern, $section_text, $matches, PREG_OFFSET_CAPTURE);
                if(!empty($is_matched)) {
                    $verse_numbers = $matches[1];
                    $verse_text_matches = $matches[2];
                    for($i = 0; $i < $is_matched; $i++) {
                        $test_verse_number = $verse_numbers[$i][0];
                        if(!empty($test_verse_number)){
                            $verse_number = (int) $test_verse_number;
                        } else {
                            if(empty($verse_number)) {
                                $error = 'Initializing verse number not found.';
                                break(2);
                            }
                        }
                        $verse_text = $verse_text_matches[$i][0];
                        if(!empty($space_count) && $space_count >= 7) {
                            $verse_text = '<span class="indent">' . $verse_text . '</span>';
                        } else {
                            $verse_text = $verse_text;
                        }
                        $verse_text = trim($verse_text);
                        $verse_text = preg_replace('/  /', ' ', $verse_text);
                        // TODO: Verse section will build our verses that span multiple lines
                        $verses[] = array('verse_number' => $verse_number, 'verse_text' => htmlentities($verse_text));
                    }
                    $test = 1;
                } else {
                    //$error = 'No verses matched.';
                    continue;
                }
            }
            if(empty($verses)) {
                $error = 'Verses array empty.';
            }
            $verses_count = count($verses);
            if(empty($error)) {
                // TODO: Iterate over $verse_text, complete any remaining prep needed, and insert into DB.
                $compacted_verses = array();
                $last_verse = 0;
                $j = 0;
                for($i = 0; $i < $verses_count; $i++) {
                    $verse_number = $verses[$i]['verse_number'];
                    if(empty($last_verse)) {
                        $last_verse = $verse_number;
                        $compacted_verses[$j] = array('verse_number' => $verse_number, 'verse_text' => $verses[$i]['verse_text']);
                    } elseif($last_verse != $verse_number) {
                        $j++;
                        $last_verse = $verse_number;
                        $compacted_verses[$j] = array('verse_number' => $verse_number, 'verse_text' => $verses[$i]['verse_text']);
                    } else {
                        $compacted_verses[$j] = array('verse_number' => $verse_number, 'verse_text' => $compacted_verses[$j]['verse_text'] . $verses[$i]['verse_text']);
                    }
                }
                $message = "Chapter: <strong>${selected_chapter}</strong><br><br>" . "Type: <strong>${selected_section}</strong><br><br><div class=\"one-col-padding\"><span class=\"${selected_section}\">";
                $verse_count = count($compacted_verses);
                for($i = 0; $i < $verse_count; $i++) {
                    if($i != $verse_count - 1) {
                        $message .= $compacted_verses[$i]['verse_number'] . ' ' . html_entity_decode($compacted_verses[$i]['verse_text']) . ' ';
                    } else {
                        $message .= $compacted_verses[$i]['verse_number'] . ' ' . html_entity_decode($compacted_verses[$i]['verse_text']);
                    }
                    $chapter_id = get_book_chapter_id($book_title, $selected_chapter, $amysql);
                    $chapter_verse_text = get_book_chapter_verse($book_title, $selected_chapter, $verse_number, $amysql);
                    $success = false;
                    // Note: Since I converted the verse_number column in the verses table to a DECIMAL(5,1), this doesn't seem to be working for updates. Probably need to account for the decimal, without having really looking at it.
                    if(empty($chapter_verse_text)) {
                        // Insert
                        $success = insert_chapter_verse($chapter_id, $compacted_verses[$i]['verse_number'], $compacted_verses[$i]['verse_text'], $segment_id, $is_poetry, $amysql);
                    } else {
                        // Update
                        $success = update_chapter_verse($chapter_id, $compacted_verses[$i]['verse_number'], $compacted_verses[$i]['verse_text'], $segment_id, $is_poetry, $amysql);
                    }
                    $test = 1;
                }
                $message .= "</span></div>";
                $segment_id++;
            } else {
                $message = 'Error: ' . $error;
            }
            /*
            // Filtering
            $section_text = strip_tags($section_text, '<strong><em><b><i><sup><p><br>');
            $section_text = preg_replace('/\r\n/', ' ', $section_text);
            $section_text = preg_replace('/  /', ' ', $section_text);
            $section_text = preg_replace('/\t+/', '', $section_text);
            preg_match('/<sup>\w<\/sup>/', $section_text, $matches, PREG_OFFSET_CAPTURE);
            if(!empty($matches)) {
                $section_text = preg_replace('/(.*)<sup>(<i><sup>\w<\/sup><\/i>)<\/sup>(.*)/s', '\1\2\3', $section_text);
            }
            $space_count = strspn($section_text, ' ');
            $section_text = trim($section_text);
            // Verse(s) extraction
            $pattern = '/(\d*)\s*(\D+)/s';
            $is_matched = preg_match_all($pattern, $section_text, $matches, PREG_OFFSET_CAPTURE);
            if(!empty($is_matched)) {
                $verse_numbers = $matches[1];
                $verse_text_matches = $matches[2];
                for($i = 0; $i < $is_matched; $i++) {
                    $test_verse_number = $verse_numbers[$i][0];
                    if(!empty($test_verse_number)){
                        $verse_number = (int) $test_verse_number;
                        $verse_text = trim($verse_text_matches[$i][0]);
                    } else {
                        $test = 1;
                    }
                    $verse_text = htmlentities($verse_text);
                }
            } else {
                // Error: No verses matched
            }
            $message = "Chapter: <strong>${selected_chapter}</strong><br><br>" . "Type: <strong>${selected_section}</strong><br><br>" . html_entity_decode($section_text);
            */
        }
    } else {
        if(empty($_SESSION['started'])) {
            $_SESSION['started'] = true;
            $selected_chapter = 1;
            $_SESSION['selected_chapter'] = $selected_chapter;
            $selected_section = "poetry";
            $_SESSION['selected_section'] = $selected_section;
            $_SESSION['segment_id'] = 1;
        } else {
            if(!empty($_SESSION['selected_chapter'])) {
                $selected_chapter = $_SESSION['selected_chapter'];
            } else {
                $selected_chapter = 1;
            }
            if(!empty($_SESSION['selected_section'])) {
                $selected_section = $_SESSION['selected_section'];
            } else {
                $selected_section = "poetry";
            }
        }
        $message = '';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>IIT DB Tool</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<link rel="stylesheet" type="text/css" href="../../styles.css" media="all">
<script type="text/javascript" src="view.js"></script>
</head> 
<body id="main_body" class="scriptures tab-heading hasGoogleVoiceExt">
	<img id="top" src="top.png" alt="">
	<div id="form_container">
		<h1><a>IIT DB Tool</a></h1>
		<form id="form_848277" class="appnitro"  method="post" action="">
		    <div class="form_description">
			    <h2>IIT DB Tool</h2>
                <br />
			    <p><?php echo $message; ?></p>
                <br />
		    </div>						
			<ul >
                <li id="li_4">
                    <label class="description" for="segment_id">Segment ID</label>
                    <div>
                        <input id="segment_id" name="segment_id" class="element text" type="text" value="<?php echo (!empty($segment_id) ? $segment_id : 1) ?>"/>
                    </div>
                </li>
			    <li id="li_3" >
		            <label class="description" for="chapter">Chapter </label>
		            <div>
		                <select class="element select small" id="chapter" name="chapter">
                            <?php
                                for ($i = 1; $i <= 66; $i++) {
                                    if($i == $selected_chapter) {
                                        $options .= "<option value=\"${i}\" selected=\"selected\">${i}</option>\r\n";
                                    } else {
                                        $options .= "<option value=\"${i}\" >${i}</option>\r\n";
                                    }
                                }
                                echo $options;
                            ?>
		                </select>
		            </div> 
		        </li>
                <li id="li_2" >
                    <label class="description" for="section_type">Section Type </label>
                    <span>
                        <?php
                            if($selected_section == "poetry")
                        ?>
			            <input id="section_type_1" name="section_type" class="element radio" type="radio" value="poetry" <?php echo ($selected_section == 'poetry' ? 'checked="checked"' : '') ?> />
                        <label class="choice" for="section_type_1">Poetry</label>
                        <input id="section_type_2" name="section_type" class="element radio" type="radio" value="prose" <?php echo ($selected_section == 'prose' ? 'checked="checked"' : '') ?> />
                        <label class="choice" for="section_type_2">Prose</label>
                    </span> 
		        </li>
                <li id="li_1" >
                    <label class="description" for="element_1">Section Text </label>
                    <div>
                        <textarea id="section_text" name="section_text" class="element textarea large"></textarea>
                    </div>
                </li>
                <li class="buttons">
			        <input type="hidden" name="form_id" value="848277" />
                    <input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		        </li>
			</ul>
		</form>	
		<div id="footer">
		</div>
	</div>
	<img id="bottom" src="bottom.png" alt="">
</body>
</html>