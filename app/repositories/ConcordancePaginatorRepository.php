<?php
class ConcordancePaginator {

    /**
     * Gets previous/next navigation html for the specified letter, direction, page area, and active tab
     *
     * @param string $letter Current chapter
     * @param string $direction Navigation direction
     * @param string $page_area Area of page needing navigation
     * @return string Navigation HTML
     */
    public static function GetNav($letter, $direction, $page_area) {
        if($direction == 'left') {
            //$nav_letter = $letter - 1;
            $nav_letter = ConcordancePaginator::_decrementLetter($letter);
        } else {
            $letter++;
            $nav_letter = $letter;
            //$nav_letter = $letter + 1;
        }

        $nav_html = ConcordancePaginator::GetNavHtml($nav_letter, $direction, $page_area);

        return $nav_html;
    }

    private static function _decrementLetter($letter) {
        return chr(ord($letter) - 1);
        //sprintf("%c", 27);
    }

    private static function _alphaToNum($str) {
        $num = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $num += ord($str[$i]);
            $num *= 26;
        }
        return $num;
    }

    /**
     * Gets navigation html for the specified chapter, direction, page area, and active tab
     *
     * @param int $nav_letter
     * @param string $direction
     * @param string $page_area
     * @return string Navigation HTML
     */
    protected static function GetNavHtml($nav_letter, $direction, $page_area) {
        $nav_class = '';
        $nav_area = '';

        switch($page_area) {
            case 'nav-links-light':
                $nav_class .= 'btn btn-default ';
                $nav_area = '-bottom';
                break;
            case 'heading-letters':
                $nav_area = '-top';
                break;
            default:
                break;
        }

        if($nav_letter != '@' && $nav_letter != 'AA') {
            if($direction == 'left') {
                $nav_class .= 'fa fa-angle-left';
                if($page_area == 'heading-letters') {
                    $nav_class .= ' heading-nav-left';
                }
                $nav_id = 'nav-left' . $nav_area;
            } else {
                $nav_class .= 'fa fa-angle-right';
                if($page_area == 'heading-letters') {
                    $nav_class .= ' heading-nav-right';
                }
                $nav_id = 'nav-right' . $nav_area;
            }
            $link = "/concordance/${nav_letter}";
        } else {
            $link = '';
            if($direction == 'left') {
                if($page_area == 'heading-letters') {
                    $nav_class .= 'heading-nav-left ';
                }
                $nav_class .= 'fa fa-angle-left disabled';
                $nav_id = 'nav-left-disabled';
            } else {
                if($page_area == 'heading-letters') {
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