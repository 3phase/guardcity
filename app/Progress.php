<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress';

    public function user(){
        return $this->belongsTo('App\\User', 'user_id');
    }

    public function planet(){
        return $this->belongsTo('App\\Planet', 'planet_id');
    }
}