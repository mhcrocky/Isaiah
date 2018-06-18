<?php

class IndexRepository {
    /**
     * Gets html for the chapter index
     *
     * @return string Chapter selection widget html
     */
    public static function GetChapterIndex() {
        return Heading::all();
    }
} 