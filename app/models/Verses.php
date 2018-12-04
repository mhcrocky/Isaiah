<?php

class Verse extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'verses';

    /**
     * Do not maintain ORM creation records for this model
     *
     * @var bool
     */
    public $timestamps = false;

    public function book() {
        return $this->hasOne('Book');
    }

    public function chapter() {
        return $this->hasOne('Chapter');
    }

    public function getVerseNumberAttribute($value) {
        return $this->_strrtrim($value, '.0');
    }

    /**
     * Strip a string from the end of a string
     *
     * @param mixed $message the input string
     * @param mixed $strip string to remove
     *
     * @return string the modified string
     */
    private function _strrtrim($message, $strip) {
        $lines = explode($strip, $message);
        $last  = '';
        do {
            $last = array_pop($lines);
        } while (empty($last) && (count($lines)));
        return implode($strip, array_merge($lines, array($last)));
    }
}
