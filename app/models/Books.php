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

    public function chapter() {
        return $this->hasMany('Chapter');
    }

    public function volume() {
        return $this->hasOne('Volume');
    }
}
