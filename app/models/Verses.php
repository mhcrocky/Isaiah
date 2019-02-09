<?php

class Verse extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'verses';

    protected $appends = array('verse_class');

    protected $highlights = [];

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
        return Helpers\strrtrim($value, '.0');
    }

    public function getVerseClassAttribute() {
        if(!empty($this->highlights[$this->verse_number])) {
            return $this->highlights[$this->verse_number];
        } else {
            return '';
        }
    }

    public function SetVerseClassAttribute($class) {
        $this->highlights[$this->verse_number] = $class;
    }
}
