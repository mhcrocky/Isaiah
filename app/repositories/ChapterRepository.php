<?php

class ChapterRepository {
    /**
     * Gets text for the chapter heading
     *
     * @param int Chapter number
     * @return string Chapter heading text
     */
    public static function GetChapterHeading($chapter_number) {
        $heading = Heading::where('id', '=', $chapter_number)->first();
        return $heading->heading_text;
    }
} 