<?php

class Volume extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'volumes';

    /**
     * Do not maintain ORM creation records for this model
     *
     * @var bool
     */
    public $timestamps = false;

    public function book() {
        return $this->hasMany('Book');
    }
}
