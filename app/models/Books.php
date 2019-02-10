<?php

class Book extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'books';

    /**
     * Do not maintain ORM creation records for this model
     *
     * @var bool
     */
    public $timestamps = false;

    public function volume() {
        return $this->hasOne('Volume');
    }

    public function chapters() {
        return $this->hasMany('Chapter');
    }

    public function verses() {
        return $this->hasManyThrough('Verse', 'Chapter');
    }
}
