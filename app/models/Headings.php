<?php

class Heading extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'iit_headings';

    /**
     * Do not maintain ORM creation records for this model
     *
     * @var bool
     */
    public $timestamps = false;

}
