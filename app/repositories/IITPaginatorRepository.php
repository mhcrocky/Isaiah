<?php
class IITPaginator {

    /**
     * Gets previous/next navigation html for the specified chapter, direction, page area, and active tab
     *
     * @param int $chapter Current chapter
     * @param string $direction Navigation direction
     * @param string $page_area Area of page needing navigation
     * @param string $active_tab Active tab
     * @return string Navigation HTML
     */
    public static function GetNav($chapter, $direction, $page_area, $active_tab = 'one_col') {
        if($direction == 'left') {
            $nav_chapter = $chapter - 1;
        } else {
            $nav_chapter = $chapter + 1;
        }

        $nav_html = IITPaginator::GetNavHtml($nav_chapter, $direction, $page_area, $active_tab);

        return $nav_html;
    }

    /**
     * Gets navigation html for the specified chapter, direction, page area, and active tab
     *
     * @param int $nav_chapter
     * @param string $direction
     * @param string $page_area
     * @param string $active_tab
     * @return string Navigation HTML
     */
    protected static function GetNavHtml($nav_chapter, $direction, $page_area, $active_tab) {
        $nav_class = '';

        switch($page_area) {
            case 'nav-links-light':
                $nav_class .= 'btn btn-default ';
                break;
            case 'heading-chapters':
                break;
            default:
                break;
        }

        if($nav_chapter >= 1 && $nav_chapter <= 66) {
            if($direction == 'left') {
                $nav_class .= 'fa fa-angle-left';
                $nav_id = 'nav-left';
            } else {
                $nav_class .= 'fa fa-angle-right';
                $nav_id = 'nav-right';
            }
            //$link = "/${nav_chapter}?section=${active_tab}";
            $link = "/${nav_chapter}";
        } else {
            $link = '';
            if($direction == 'left') {
                $nav_class .= 'fa fa-angle-left disabled';
                $nav_id = 'nav-left-disabled';
            } else {
                $nav_class .= 'fa fa-angle-right disabled';
                $nav_id = 'nav-right-disabled';
            }
        }

        $nav_html = "<a id=\"${nav_id}\" class=\"${nav_class}\" href=\"${link}\"></a>";

        return $nav_html;
    }

}