<?php

class Chapter extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chapters';

    /**
     * Do not maintain ORM creation records for this model
     *
     * @var bool
     */
    public $timestamps = false;

    public function verses() {
        return $this->hasMany('Verse');
    }

    public function heading() {
        return $this->hasOne('Heading');
    }
}
