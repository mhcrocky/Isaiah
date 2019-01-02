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

    /**
     * Get Book Chapter Verses
     *
     * @param $book_abbr
     * @param $chapter_number
     * @return Verse
     */
    public static function GetChapterVerses($book_abbr, $chapter_number) {
        return Book::where('book_lds_url', '=', $book_abbr)->first()->chapters()->where('chapter_number', '=', $chapter_number)->first()->verses;
    }


} 