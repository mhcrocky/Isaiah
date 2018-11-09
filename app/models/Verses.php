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

    public function chapter() {
        return $this->hasOne('Chapter');
    }
}
