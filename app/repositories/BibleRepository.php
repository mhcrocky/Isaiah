<?php

class BibleRepository {
    /**
     * Gets books from the Old Testament
     *
     * @return Book Bible books
     */
    public static function GetKJVBookIndex() {
        return Book::where('volume_id', '<=', 2)->get();
    }

    /**
     * Gets books from the Old Testament
     *
     * @return Book Bible books
     */
    public static function GetOTBookIndex() {
        return Book::where('volume_id', '=', 1)->get();
    }

    /**
     * Gets books from the New Testament
     *
     * @return Book Bible books
     */
    public static function GetNTBookIndex() {
        return Book::where('volume_id', '=', 2)->get();
    }

    /**
     * Get Book's title from abbreviation
     *
     * @param $book_abbr
     * @return string
     */
    public static function GetBookTitleFromAbbr($book_abbr) {
        return Book::where('book_lds_url', '=', $book_abbr)->first()->book_title;
    }

    /**
     * Get Book's Chapters
     *
     * @param $book_abbr
     * @return Chapter
     */
    public static function GetBookChapters($book_abbr) {
        return Book::where('book_lds_url', '=', $book_abbr)->first()->chapters;
    }

    public static function GetBookChaptersCount($book_abbr) {
        return Book::where('book_lds_url', '=', $book_abbr)->first()->chapters()->count();
    }

    /**
     * Get Book Chapter Verses
     *
     * @param $book_abbr
     * @param $chapter_number
     * @param $reference_input
     * @return Verse
     */
    public static function GetChapterVerses($book_abbr, $chapter_number, $reference_input) {
        $references = BibleRepository::getReferences($reference_input);

        $verses = Book::where('book_lds_url', '=', $book_abbr)->first()->chapters()->where('chapter_number', '=', $chapter_number)->first()->verses;

        $verse_count = count($verses);
        for($i = 0; $i < $verse_count; $i++) {
            if(in_array($verses[$i]->verse_number, $references)) {
                $verses[$i]->SetVerseClassAttribute('highlight');
            }
        }

        return $verses;
    }

    /**
     * Get references from URL
     *
     * @param $reference_input
     * @return array
     */
    private static function getReferences($reference_input) {
        $references = [];

        $is_error = false;

        $verse_limit = 177;

        if(!empty($reference_input)) {
            if(preg_match_all('/(\d+-\d+|\d+)|,(\d+-\d+|\d+)/', $reference_input, $matches)) {
                if(!empty($matches)) {
                    foreach($matches[0] as $reference_match) {
                        $fixed_reference = ltrim($reference_match, ',');
                        if(strpos($fixed_reference, '-') !== false) {
                            $parts = explode('-', $fixed_reference);
                            if(!empty($parts[0]) && !empty($parts[1])) {
                                $start = $parts[0];
                                if($start > 0 && $start < $verse_limit) {
                                    $end = $parts[1];
                                    if($end > $start && $end < $verse_limit) {
                                        for ($i = $start; $i <= $end; $i++) {
                                            if (!in_array($i, $references)) {
                                                $references[] = "{$i}";
                                            }
                                        }
                                    } else {
                                        $is_error = true;
                                    }
                                } else {
                                    $is_error = true;
                                }
                            }
                        } else {
                            if (!in_array($fixed_reference, $references)) {
                                $references[] = $fixed_reference;
                            }
                        }
                    }
                }
            }
        }

        if($is_error == true) {
            App::abort(403, 'Unauthorized action.');
        } else {
            return $references;
        }
    }
} 