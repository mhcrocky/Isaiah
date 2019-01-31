<?php
class KJVPaginator {

    public static $limit = 0;

    /**
     * Gets previous/next navigation html for the specified chapter, direction, page area, and active tab
     *
     * @param string $book_abbr KJV book abbreviation
     * @param int $chapter Current chapter
     * @param string $direction Navigation direction
     * @param string $page_area Area of page needing navigation
     * @return string Navigation HTML
     */
    public static function GetNav($book_abbr, $chapter, $direction, $page_area) {
        if($direction == 'left') {
            --$chapter;
        } else {
            ++$chapter;
        }

        $nav_html = KJVPaginator::GetNavHtml($book_abbr, $chapter, $direction, $page_area);

        return $nav_html;
    }

    /**
     * Gets navigation html for the specified chapter, direction, page area, and active tab
     *
     * @param string $nav_book_abbr
     * @param int $nav_chapter
     * @param string $direction
     * @param string $page_area
     * @return string Navigation HTML
     */
    protected static function GetNavHtml($nav_book_abbr, $nav_chapter, $direction, $page_area) {
        $nav_class = '';
        $nav_area = '';

        switch($page_area) {
            case 'nav-links-light':
                $nav_class .= 'btn btn-default ';
                $nav_area = '-bottom';
                break;
            case 'heading-chapters':
                $nav_area = '-top';
                break;
            default:
                break;
        }

        $limit = KJVPaginator::$limit;
        if($limit == 0) {
            $limit = BibleRepository::GetBookChaptersCount($nav_book_abbr);
            KJVPaginator::$limit = $limit;
        }

        if($nav_chapter >= 1 && $nav_chapter <= $limit) {
            if($direction == 'left') {
                $nav_class .= 'fa fa-angle-left';
                if($page_area == 'heading-chapters') {
                    $nav_class .= ' heading-nav-left';
                }
                $nav_id = 'nav-left' . $nav_area;
            } else {
                $nav_class .= 'fa fa-angle-right';
                if($page_area == 'heading-chapters') {
                    $nav_class .= ' heading-nav-right';
                }
                $nav_id = 'nav-right' . $nav_area;
            }
            $link = "/bible/{$nav_book_abbr}/{$nav_chapter}";
        } else {
            $link = '';
            if($direction == 'left') {
                if($page_area == 'heading-chapters') {
                    $nav_class .= 'heading-nav-left ';
                }
                $nav_class .= 'fa fa-angle-left disabled';
                $nav_id = 'nav-left-disabled';
            } else {
                if($page_area == 'heading-chapters') {
                    $nav_class .= 'heading-nav-right ';
                }
                $nav_class .= 'fa fa-angle-right disabled';
                $nav_id = 'nav-right-disabled';
            }
        }

        $nav_html = "<a id=\"${nav_id}\" class=\"${nav_class}\" href=\"${link}\"></a>";

        return $nav_html;
    }

}